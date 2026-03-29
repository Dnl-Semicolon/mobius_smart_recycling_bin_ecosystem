import Foundation

/// Manages in-app notification state — fetching, pagination, read status, unread badge count.
@MainActor
@Observable
final class NotificationManager {
    private(set) var notifications: [AppNotification] = []
    private(set) var unreadCount: Int = 0
    private(set) var isLoading = false
    private(set) var hasMore = true
    private var currentPage = 1

    private let api = APIClient()

    // MARK: - Fetch

    func fetchNotifications(refresh: Bool = false) async {
        if refresh {
            currentPage = 1
            hasMore = true
        }
        guard hasMore else { return }
        isLoading = true
        defer { isLoading = false }

        do {
            let response: PaginatedResponse<AppNotification> = try await api.get("/notifications?page=\(currentPage)")
            if refresh {
                notifications = response.data
            } else {
                notifications.append(contentsOf: response.data)
            }
            hasMore = response.currentPage < response.lastPage
            currentPage = response.currentPage + 1
        } catch {
            // Silently fail — notifications are non-critical
        }
    }

    func loadMore() async {
        guard !isLoading, hasMore else { return }
        await fetchNotifications()
    }

    // MARK: - Unread Count

    func fetchUnreadCount() async {
        do {
            let response: UnreadCountResponse = try await api.get("/notifications/unread-count")
            unreadCount = response.unreadCount
        } catch {
            // Silently fail
        }
    }

    // MARK: - Mark As Read

    func markAsRead(_ notification: AppNotification) async {
        guard !notification.isRead else { return }

        // Optimistic update
        if let index = notifications.firstIndex(where: { $0.id == notification.id }) {
            notifications[index] = AppNotification(
                id: notification.id,
                type: notification.type,
                title: notification.title,
                body: notification.body,
                data: notification.data,
                isRead: true,
                readAt: Date(),
                createdAt: notification.createdAt
            )
            unreadCount = max(0, unreadCount - 1)
        }

        do {
            let _: MessageResponse = try await api.post("/notifications/\(notification.id)/read")
        } catch {
            // Revert on failure
            if let index = notifications.firstIndex(where: { $0.id == notification.id }) {
                notifications[index] = notification
                unreadCount += 1
            }
        }
    }

    func markAllAsRead() async {
        let previousNotifications = notifications
        let previousCount = unreadCount

        // Optimistic update
        notifications = notifications.map {
            AppNotification(
                id: $0.id, type: $0.type, title: $0.title, body: $0.body,
                data: $0.data, isRead: true, readAt: $0.readAt ?? Date(), createdAt: $0.createdAt
            )
        }
        unreadCount = 0

        do {
            let _: MessageResponse = try await api.post("/notifications/read-all")
        } catch {
            // Revert on failure
            notifications = previousNotifications
            unreadCount = previousCount
        }
    }

    // MARK: - Mock

    nonisolated static func mockWithNotifications() -> NotificationManager {
        MainActor.assumeIsolated {
            let manager = NotificationManager()
            manager.notifications = [.mockUnread, .mockRead]
            manager.unreadCount = 1
            manager.isLoading = false
            return manager
        }
    }
}

// MARK: - Response Types

struct PaginatedResponse<T: Decodable & Sendable>: Decodable, Sendable {
    let data: [T]
    let currentPage: Int
    let lastPage: Int

    enum CodingKeys: String, CodingKey {
        case data
        case currentPage = "current_page"
        case lastPage = "last_page"
    }
}

private struct UnreadCountResponse: Decodable, Sendable {
    let unreadCount: Int

    enum CodingKeys: String, CodingKey {
        case unreadCount = "unread_count"
    }
}
