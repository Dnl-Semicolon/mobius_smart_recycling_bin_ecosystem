import Foundation

struct PickupRequest: Codable, Identifiable, Sendable {
    let id: Int
    var binId: Int
    var binSerial: String?
    var outletName: String?
    var status: PickupStatus
    var claimedBy: Int?
    var claimedAt: Date?
    var completedAt: Date?
    var createdAt: Date?

    enum CodingKeys: String, CodingKey {
        case id, status
        case binId = "bin_id"
        case binSerial = "bin_serial"
        case outletName = "outlet_name"
        case claimedBy = "claimed_by"
        case claimedAt = "claimed_at"
        case completedAt = "completed_at"
        case createdAt = "created_at"
    }
}

enum PickupStatus: String, Codable {
    case pending = "pending"
    case claimed = "claimed"
    case completed = "completed"
    case cancelled = "cancelled"

    var label: String {
        rawValue.capitalized
    }

    var color: String {
        switch self {
        case .pending: "orange"
        case .claimed: "blue"
        case .completed: "green"
        case .cancelled: "gray"
        }
    }
}
