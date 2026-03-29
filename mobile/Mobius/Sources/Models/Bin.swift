import Foundation

struct Bin: Codable, Identifiable, Sendable {
    let id: Int
    var serialNumber: String
    var status: BinStatus
    var fillLevel: Int
    var outletId: Int?
    var outletName: String?

    enum CodingKeys: String, CodingKey {
        case id, status
        case serialNumber = "serial_number"
        case fillLevel = "fill_level"
        case outletId = "outlet_id"
        case outletName = "outlet_name"
    }

    var fillPercentage: Double {
        Double(fillLevel) / 100.0
    }

    var needsPickup: Bool {
        fillLevel >= 80
    }
}

enum BinStatus: String, Codable {
    case active = "active"
    case inactive = "inactive"
    case maintenance = "maintenance"

    var label: String {
        rawValue.capitalized
    }
}

// MARK: - Mock Data

extension Bin {
    static let mock001 = Bin(
        id: 1,
        serialNumber: "MBR-2026-001",
        status: .active,
        fillLevel: 45,
        outletId: 1,
        outletName: "Starbucks Gurney Plaza"
    )

    static let mock002 = Bin(
        id: 2,
        serialNumber: "MBR-2026-002",
        status: .active,
        fillLevel: 12,
        outletId: 1,
        outletName: "Starbucks Gurney Plaza"
    )

    static let mock003 = Bin(
        id: 3,
        serialNumber: "MBR-2026-003",
        status: .active,
        fillLevel: 83,
        outletId: 2,
        outletName: "CHAGEE Gurney Plaza"
    )

    static let mockList = [mock001, mock002, mock003]
}
