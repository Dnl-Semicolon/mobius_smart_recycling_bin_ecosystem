import Foundation

// MARK: - Reward (GET /customer/rewards)

struct Reward: Codable, Identifiable, Sendable {
    let id: Int
    let name: String
    let description: String?
    let pointsCost: Int
    let stock: Int
    let imagePath: String?
    let expiresAt: Date?
    let brand: RewardBrand?

    enum CodingKeys: String, CodingKey {
        case id, name, description, stock, brand
        case pointsCost = "points_cost"
        case imagePath = "image_path"
        case expiresAt = "expires_at"
    }
}

// MARK: - Reward Brand (nested in reward response)

struct RewardBrand: Codable, Sendable {
    let id: Int
    let name: String
    let slug: String
    let color: String?
}
