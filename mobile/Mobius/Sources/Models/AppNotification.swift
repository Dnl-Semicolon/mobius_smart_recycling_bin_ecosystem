import SwiftUI

struct AppNotification: Codable, Identifiable, Sendable, Equatable {
    let id: Int
    let type: NotificationType
    let title: String
    let body: String
    let data: [String: String]?
    let isRead: Bool
    let readAt: Date?
    let createdAt: Date

    enum CodingKeys: String, CodingKey {
        case id, type, title, body, data
        case isRead = "is_read"
        case readAt = "read_at"
        case createdAt = "created_at"
    }
}

enum NotificationType: String, Codable, Sendable {
    case pointsEarned = "points_earned"
    case streakMilestone = "streak_milestone"
    case achievement = "achievement"
    case pickupComplete = "pickup_complete"
    case system = "system"
    case welcome = "welcome"

    var icon: String {
        switch self {
        case .pointsEarned: "leaf.fill"
        case .streakMilestone: "flame.fill"
        case .achievement: "trophy.fill"
        case .pickupComplete: "shippingbox.fill"
        case .system: "bell.fill"
        case .welcome: "hand.wave.fill"
        }
    }

    var iconColor: Color {
        switch self {
        case .pointsEarned: .green
        case .streakMilestone: .orange
        case .achievement: .yellow
        case .pickupComplete: .blue
        case .system: .gray
        case .welcome: .purple
        }
    }
}

// MARK: - Mock Data

extension AppNotification {
    static let mockUnread = AppNotification(
        id: 1,
        type: .pointsEarned,
        title: "Points Earned!",
        body: "You earned 10 points for recycling Paper Cup.",
        data: ["points": "10", "waste_type": "paper_cup"],
        isRead: false,
        readAt: nil,
        createdAt: Date().addingTimeInterval(-3600)
    )

    static let mockRead = AppNotification(
        id: 2,
        type: .welcome,
        title: "Welcome to Mobius!",
        body: "Start recycling to earn points and rewards. Every item counts!",
        data: nil,
        isRead: true,
        readAt: Date().addingTimeInterval(-7200),
        createdAt: Date().addingTimeInterval(-86400)
    )
}
