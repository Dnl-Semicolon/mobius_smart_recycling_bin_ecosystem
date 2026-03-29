import Foundation

// MARK: - Redemption (GET /customer/redemptions — paginated)

struct Redemption: Codable, Identifiable, Sendable {
    let id: Int
    let pointsSpent: Int
    let voucherCode: String
    let status: String  // "pending", "fulfilled", "expired"
    let redeemedAt: Date?
    let expiresAt: Date?
    let reward: RedemptionReward?

    enum CodingKeys: String, CodingKey {
        case id, status, reward
        case pointsSpent = "points_spent"
        case voucherCode = "voucher_code"
        case redeemedAt = "redeemed_at"
        case expiresAt = "expires_at"
    }
}

// MARK: - Nested Reward (eager-loaded in redemption response)

struct RedemptionReward: Codable, Sendable {
    let id: Int
    let name: String
    let brand: RedemptionBrand?
}

struct RedemptionBrand: Codable, Sendable {
    let id: Int
    let name: String
    let slug: String
}

// MARK: - Redeem Result (POST /customer/rewards/{id}/redeem)

struct RedeemResult: Codable, Sendable {
    let voucherCode: String
    let rewardName: String
    let pointsSpent: Int
    let expiresAt: Date?
    let newBalance: Int

    enum CodingKeys: String, CodingKey {
        case voucherCode = "voucher_code"
        case rewardName = "reward_name"
        case pointsSpent = "points_spent"
        case expiresAt = "expires_at"
        case newBalance = "new_balance"
    }
}
