import Foundation

struct User: Codable, Identifiable, Sendable, Equatable {
    let id: Int
    var name: String
    var displayName: String?
    var email: String
    var phone: String?
    var bio: String?
    var avatarUrl: String?
    var roles: [UserRole]
    var pointsBalance: Int
    var currentStreak: Int
    var longestStreak: Int
    var lastRecycledAt: Date?
    var createdAt: Date?

    enum CodingKeys: String, CodingKey {
        case id, name, email, phone, bio, roles
        case displayName = "display_name"
        case avatarUrl = "avatar_url"
        case pointsBalance = "points_balance"
        case currentStreak = "current_streak"
        case longestStreak = "longest_streak"
        case lastRecycledAt = "last_recycled_at"
        case createdAt = "created_at"
    }

    var hasMultipleRoles: Bool {
        roles.count > 1
    }

    func hasRole(_ role: UserRole) -> Bool {
        roles.contains(role)
    }

    /// The primary (first) role — used as default on login
    var primaryRole: UserRole {
        roles.first ?? .publicUser
    }
}

// MARK: - Mock Data

extension User {
    static let mock = User(
        id: 1,
        name: "Daniel Tan",
        displayName: "Dan",
        email: "daniel.tan@email.com",
        phone: "+60123456789",
        bio: "Recycling enthusiast from Penang",
        avatarUrl: nil,
        roles: [.publicUser, .storeOwner, .collector],
        pointsBalance: 8700,
        currentStreak: 45,
        longestStreak: 67,
        lastRecycledAt: Date().addingTimeInterval(-3600),
        createdAt: Calendar.current.date(from: DateComponents(year: 2024, month: 3, day: 15))
    )

    static let publicOnly = User(
        id: 2,
        name: "Ahmad Razak",
        displayName: nil,
        email: "ahmad@example.com",
        phone: "+60198765432",
        bio: nil,
        avatarUrl: nil,
        roles: [.publicUser],
        pointsBalance: 1250,
        currentStreak: 7,
        longestStreak: 12,
        lastRecycledAt: Date().addingTimeInterval(-86400),
        createdAt: nil
    )
}
