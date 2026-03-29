import Foundation
import CoreLocation

struct Outlet: Codable, Identifiable, Sendable {
    let id: Int
    var name: String
    var address: String
    var latitude: Double
    var longitude: Double
    var contactName: String?
    var contactPhone: String?
    var operatingHours: String?
    var contractStatus: String?
    var notes: String?

    enum CodingKeys: String, CodingKey {
        case id, name, address, latitude, longitude, notes
        case contactName = "contact_name"
        case contactPhone = "contact_phone"
        case operatingHours = "operating_hours"
        case contractStatus = "contract_status"
    }

    var coordinate: CLLocationCoordinate2D {
        CLLocationCoordinate2D(latitude: latitude, longitude: longitude)
    }
}

// MARK: - Mock Data

extension Outlet {
    static let starbucks = Outlet(
        id: 1,
        name: "Starbucks Gurney Plaza",
        address: "Gurney Plaza, Persiaran Gurney, 10250 George Town, Penang",
        latitude: 5.4370,
        longitude: 100.3100,
        contactName: "Lim Bee Hoon",
        contactPhone: "012-483 7291",
        operatingHours: "10:00-22:00",
        contractStatus: "active",
        notes: "High traffic mall location"
    )

    static let chagee = Outlet(
        id: 2,
        name: "CHAGEE Gurney Plaza",
        address: "Gurney Plaza, Persiaran Gurney, 10250 George Town, Penang",
        latitude: 5.4372,
        longitude: 100.3098,
        contactName: "Chen Wei Ling",
        contactPhone: "016-754 3182",
        operatingHours: "10:00-22:00",
        contractStatus: "active",
        notes: nil
    )

    static let mockList = [starbucks, chagee]
}
