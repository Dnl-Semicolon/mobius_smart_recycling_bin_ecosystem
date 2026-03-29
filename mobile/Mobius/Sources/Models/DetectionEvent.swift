import Foundation

struct DetectionEvent: Codable, Identifiable, Sendable {
    let id: Int
    var binId: Int
    var userId: Int?
    var wasteType: WasteType?
    var confidence: Int?
    var weightG: Int?
    var detectedBrandId: Int?
    var imagePath: String?
    var pointsEarned: Int?
    var detectedBrand: DetectedBrand?
    var createdAt: Date?

    enum CodingKeys: String, CodingKey {
        case id
        case binId = "bin_id"
        case userId = "user_id"
        case wasteType = "waste_type"
        case confidence
        case weightG = "weight_g"
        case detectedBrandId = "detected_brand_id"
        case imagePath = "image_path"
        case pointsEarned = "points_earned"
        case detectedBrand = "detected_brand"
        case createdAt = "created_at"
    }
}

struct DetectedBrand: Codable, Identifiable, Sendable {
    let id: Int
    let name: String
    let slug: String
    var logoPath: String?

    enum CodingKeys: String, CodingKey {
        case id, name, slug
        case logoPath = "logo_path"
    }
}
