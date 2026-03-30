import Foundation

/// Manages customer-facing data — stats, recycling history, leaderboard, bin scanning.
@MainActor
@Observable
final class CustomerService {
    private(set) var stats: CustomerStats?
    private(set) var history: [RecyclingHistoryItem] = []
    private(set) var leaderboard: [LeaderboardEntry] = []
    private(set) var binSession: BinSession?

    private(set) var isLoadingStats = false
    private(set) var isLoadingHistory = false
    private(set) var isLoadingLeaderboard = false
    private(set) var isScanning = false

    private(set) var hasMoreHistory = true
    private var historyPage = 1

    private(set) var feedbackSent: Set<Int> = []

    var error: String?

    private let api = APIClient()

    // MARK: - Stats

    func fetchStats() async {
        isLoadingStats = true
        defer { isLoadingStats = false }

        do {
            stats = try await api.get("/customer/stats")
        } catch {
            self.error = error.localizedDescription
        }
    }

    // MARK: - History (paginated)

    func fetchHistory(refresh: Bool = false) async {
        if refresh {
            historyPage = 1
            hasMoreHistory = true
        }
        guard hasMoreHistory, !isLoadingHistory else { return }
        isLoadingHistory = true
        defer { isLoadingHistory = false }

        do {
            let response: PaginatedResponse<RecyclingHistoryItem> = try await api.get("/customer/history?page=\(historyPage)")
            if refresh {
                history = response.data
            } else {
                history.append(contentsOf: response.data)
            }
            hasMoreHistory = response.currentPage < response.lastPage
            historyPage = response.currentPage + 1
        } catch {
            self.error = error.localizedDescription
        }
    }

    func loadMoreHistory() async {
        guard !isLoadingHistory, hasMoreHistory else { return }
        await fetchHistory()
    }

    // MARK: - Leaderboard

    func fetchLeaderboard() async {
        isLoadingLeaderboard = true
        defer { isLoadingLeaderboard = false }

        do {
            let response: APIDataResponse<[LeaderboardEntry]> = try await api.get("/customer/leaderboard")
            leaderboard = response.data
        } catch {
            self.error = error.localizedDescription
        }
    }

    // MARK: - Scan Bin

    func scanBin(serial: String) async -> BinSession? {
        isScanning = true
        defer { isScanning = false }

        do {
            let body = ScanRequest(binSerial: serial)
            let response: APIMessageResponse<BinSession> = try await api.post("/customer/scan", body: body)
            binSession = response.data
            HapticManager.notification(.success)
            return response.data
        } catch {
            self.error = error.localizedDescription
            HapticManager.notification(.error)
            return nil
        }
    }

    // MARK: - Poll for Detection

    /// Poll history for a new detection that occurred after `since`.
    /// Returns the detected item, or nil if nothing found within ~60s.
    func pollForDetection(since: Date) async -> RecyclingHistoryItem? {
        let maxAttempts = 20
        let interval: UInt64 = 3_000_000_000 // 3 seconds

        for _ in 0..<maxAttempts {
            try? await Task.sleep(nanoseconds: interval)

            do {
                let response: PaginatedResponse<RecyclingHistoryItem> = try await api.get("/customer/history?page=1")
                if let latest = response.data.first,
                   let detectedAt = latest.detectedAt,
                   detectedAt > since {
                    return latest
                }
            } catch {
                // Keep polling on error
            }
        }
        return nil
    }

    // MARK: - Detection Feedback

    func submitFeedback(detectionId: Int, accurate: Bool) async {
        let body = FeedbackRequest(accurate: accurate)
        do {
            let _: APIMessageResponse<FeedbackData> = try await api.post("/detection-events/\(detectionId)/feedback", body: body)
            feedbackSent.insert(detectionId)
            HapticManager.notification(.success)
        } catch {
            self.error = error.localizedDescription
        }
    }

    // MARK: - Helpers

    /// Group history items by day for display.
    var groupedHistory: [(date: String, items: [RecyclingHistoryItem])] {
        let calendar = Calendar.current
        let now = calendar.startOfDay(for: Date())
        let yesterday = calendar.date(byAdding: .day, value: -1, to: now)!

        let formatter = DateFormatter()
        formatter.dateFormat = "d MMM yyyy"

        var groups: [(key: String, items: [RecyclingHistoryItem])] = []
        var currentKey: String?
        var currentItems: [RecyclingHistoryItem] = []

        for item in history {
            let itemDate = item.detectedAt ?? Date()
            let dayStart = calendar.startOfDay(for: itemDate)

            let key: String
            if dayStart == now {
                key = "Today"
            } else if dayStart == yesterday {
                key = "Yesterday"
            } else {
                key = formatter.string(from: itemDate)
            }

            if key == currentKey {
                currentItems.append(item)
            } else {
                if let k = currentKey {
                    groups.append((key: k, items: currentItems))
                }
                currentKey = key
                currentItems = [item]
            }
        }

        if let k = currentKey {
            groups.append((key: k, items: currentItems))
        }

        return groups.map { (date: $0.key, items: $0.items) }
    }

    // MARK: - Mock

    nonisolated static func mockWithData() -> CustomerService {
        MainActor.assumeIsolated {
            let service = CustomerService()
            service.stats = CustomerStats(
                pointsBalance: 8700,
                currentStreak: 45,
                longestStreak: 67,
                totalDetections: 127,
                co2SavedGrams: 3175
            )
            service.isLoadingStats = false
            return service
        }
    }

    nonisolated static func mockEmpty() -> CustomerService {
        MainActor.assumeIsolated {
            CustomerService()
        }
    }
}

// MARK: - Request Types

private struct ScanRequest: Encodable, Sendable {
    let binSerial: String
}

private struct FeedbackRequest: Encodable, Sendable {
    let accurate: Bool
}

struct FeedbackData: Decodable, Sendable {
    let id: Int
    let feedbackAccurate: Bool

    enum CodingKeys: String, CodingKey {
        case id
        case feedbackAccurate = "feedback_accurate"
    }
}
