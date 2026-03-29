import Foundation

// MARK: - Collector Stats (GET /collector/stats)

struct CollectorStats: Codable, Sendable {
    let totalCompleted: Int
    let thisWeek: Int
    let activeNow: Int
    let avgMinutes: Int?

    enum CodingKeys: String, CodingKey {
        case totalCompleted = "total_completed"
        case thisWeek = "this_week"
        case activeNow = "active_now"
        case avgMinutes = "avg_minutes"
    }
}

// MARK: - Collector Pickups Response (GET /collector/pickups)

struct CollectorPickupsData: Codable, Sendable {
    let available: [PickupItem]
    let active: [PickupItem]
    let history: [PickupItem]
}

/// Pickup item as returned by the PickupRequestResource with nested bin.
struct PickupItem: Codable, Identifiable, Sendable {
    let id: Int
    var status: PickupStatus
    var bin: NestedBin?
    var claimedAt: Date?
    var completedAt: Date?
    var createdAt: Date?

    enum CodingKeys: String, CodingKey {
        case id, status, bin
        case claimedAt = "claimed_at"
        case completedAt = "completed_at"
        case createdAt = "created_at"
    }

    var binSerial: String { bin?.serialNumber ?? "Unknown" }
    var outletName: String { bin?.currentAssignment?.outlet?.name ?? "Unknown outlet" }
    var fillLevel: Int { bin?.fillLevel ?? 0 }

    struct NestedBin: Codable, Sendable {
        let id: Int
        var serialNumber: String
        var fillLevel: Int
        var status: String?
        var isReadyForPickup: Bool?
        var currentAssignment: NestedAssignment?

        enum CodingKeys: String, CodingKey {
            case id, status
            case serialNumber = "serial_number"
            case fillLevel = "fill_level"
            case isReadyForPickup = "is_ready_for_pickup"
            case currentAssignment = "current_assignment"
        }
    }

    struct NestedAssignment: Codable, Sendable {
        let outlet: NestedOutlet?
    }

    struct NestedOutlet: Codable, Sendable {
        let id: Int
        let name: String
    }
}
