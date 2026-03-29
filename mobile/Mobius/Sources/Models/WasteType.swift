import SwiftUI

enum WasteType: String, Codable, CaseIterable, Identifiable {
    case paperCup = "paper_cup"
    case plasticCup = "plastic_cup"
    case lid = "lid"
    case straw = "straw"
    case napkin = "napkin"
    case liquidWaste = "liquid_waste"

    var id: String { rawValue }

    var label: String {
        switch self {
        case .paperCup: "Paper Cup"
        case .plasticCup: "Plastic Cup"
        case .lid: "Lid"
        case .straw: "Straw"
        case .napkin: "Napkin"
        case .liquidWaste: "Liquid Waste"
        }
    }

    var icon: String {
        switch self {
        case .paperCup: "cup.and.saucer.fill"
        case .plasticCup: "takeoutbag.and.cup.and.straw.fill"
        case .lid: "circle.fill"
        case .straw: "line.diagonal"
        case .napkin: "doc.fill"
        case .liquidWaste: "drop.fill"
        }
    }

    var iconColor: Color {
        switch self {
        case .paperCup: .brown
        case .plasticCup: .blue
        case .lid: .gray
        case .straw: .orange
        case .napkin: .mint
        case .liquidWaste: .cyan
        }
    }

    var points: Int {
        switch self {
        case .paperCup: 10
        case .plasticCup: 10
        case .lid: 5
        case .straw: 3
        case .napkin: 3
        case .liquidWaste: 2
        }
    }

    var co2Impact: Double {
        switch self {
        case .paperCup: 0.024
        case .plasticCup: 0.030
        case .lid: 0.008
        case .straw: 0.004
        case .napkin: 0.006
        case .liquidWaste: 0.002
        }
    }
}
