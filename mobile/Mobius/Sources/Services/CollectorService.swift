import Foundation

/// Manages collector-facing data — pickups (available/active/history) and performance stats.
@MainActor
@Observable
final class CollectorService {
    private(set) var available: [PickupItem] = []
    private(set) var active: [PickupItem] = []
    private(set) var pickupHistory: [PickupItem] = []
    private(set) var stats: CollectorStats?

    private(set) var isLoadingPickups = false
    private(set) var isLoadingStats = false

    var error: String?

    private let api = APIClient()

    // MARK: - Pickups

    func fetchPickups() async {
        isLoadingPickups = true
        defer { isLoadingPickups = false }

        do {
            let response: APIMessageResponse<CollectorPickupsData> = try await api.get("/collector/pickups")
            available = response.data.available
            active = response.data.active
            pickupHistory = response.data.history
        } catch {
            self.error = error.localizedDescription
        }
    }

    // MARK: - Stats

    func fetchStats() async {
        isLoadingStats = true
        defer { isLoadingStats = false }

        do {
            let response: APIMessageResponse<CollectorStats> = try await api.get("/collector/stats")
            stats = response.data
        } catch {
            self.error = error.localizedDescription
        }
    }

    // MARK: - Actions

    func claimPickup(_ id: Int) async -> Bool {
        do {
            let response: APIMessageResponse<PickupItem> = try await api.post("/collector/pickups/\(id)/claim")
            // Move from available to active
            available.removeAll { $0.id == id }
            active.append(response.data)
            HapticManager.notification(.success)
            return true
        } catch {
            self.error = error.localizedDescription
            HapticManager.notification(.error)
            return false
        }
    }

    func completePickup(_ id: Int) async -> Bool {
        do {
            let response: APIMessageResponse<PickupItem> = try await api.post("/collector/pickups/\(id)/complete")
            // Move from active to history
            active.removeAll { $0.id == id }
            pickupHistory.insert(response.data, at: 0)
            HapticManager.notification(.success)
            return true
        } catch {
            self.error = error.localizedDescription
            HapticManager.notification(.error)
            return false
        }
    }

    // MARK: - Mock

    nonisolated static func mockWithData() -> CollectorService {
        MainActor.assumeIsolated {
            let service = CollectorService()
            service.stats = CollectorStats(
                totalCompleted: 12,
                thisWeek: 3,
                activeNow: 0,
                avgMinutes: 24
            )
            return service
        }
    }

    nonisolated static func mockEmpty() -> CollectorService {
        MainActor.assumeIsolated {
            CollectorService()
        }
    }
}
