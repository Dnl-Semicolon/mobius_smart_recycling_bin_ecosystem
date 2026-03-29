import Foundation

// MARK: - Customer Stats (GET /customer/stats)

struct CustomerStats: Codable, Sendable {
    let pointsBalance: Int
    let currentStreak: Int
    let longestStreak: Int
    let totalDetections: Int
    let co2SavedGrams: Int

    enum CodingKeys: String, CodingKey {
        case pointsBalance = "points_balance"
        case currentStreak = "current_streak"
        case longestStreak = "longest_streak"
        case totalDetections = "total_detections"
        case co2SavedGrams = "co2_saved_grams"
    }

    /// CO₂ saved formatted as kg string
    var co2SavedFormatted: String {
        let kg = Double(co2SavedGrams) / 1000.0
        if kg >= 1 {
            return String(format: "%.1fkg", kg)
        }
        return "\(co2SavedGrams)g"
    }
}

// MARK: - Recycling History Item (GET /customer/history — paginated)

struct RecyclingHistoryItem: Codable, Identifiable, Sendable {
    let id: Int
    let wasteType: WasteType?
    let wasteTypeLabel: String?
    let confidence: Int?
    let binSerial: String?
    let detectedBrandName: String?
    let brandMatch: String?  // "match", "competitor", or nil
    let detectedAt: Date?
    let pointsEarned: Int

    enum CodingKeys: String, CodingKey {
        case id, confidence
        case wasteType = "waste_type"
        case wasteTypeLabel = "waste_type_label"
        case binSerial = "bin_serial"
        case detectedBrandName = "detected_brand_name"
        case brandMatch = "brand_match"
        case detectedAt = "detected_at"
        case pointsEarned = "points_earned"
    }
}

// MARK: - Leaderboard Entry (GET /customer/leaderboard)

struct LeaderboardEntry: Codable, Identifiable, Sendable {
    let rank: Int
    let name: String
    let points: Int
    let currentStreak: Int
    let longestStreak: Int

    var id: Int { rank }

    enum CodingKeys: String, CodingKey {
        case rank, name, points
        case currentStreak = "current_streak"
        case longestStreak = "longest_streak"
    }
}

// MARK: - Bin Session (POST /customer/scan)

struct BinSession: Codable, Sendable {
    let binId: Int
    let binSerial: String
    let outletName: String?
    let sessionExpiresIn: Int

    enum CodingKeys: String, CodingKey {
        case binId = "bin_id"
        case binSerial = "bin_serial"
        case outletName = "outlet_name"
        case sessionExpiresIn = "session_expires_in"
    }
}
