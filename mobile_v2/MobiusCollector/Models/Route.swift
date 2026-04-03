import Foundation

struct CollectionRoute: Codable, Identifiable, Sendable {
    let id: Int
    let status: String
    let depotName: String?
    let depotLatitude: Double?
    let depotLongitude: Double?
    let totalDistanceKm: Double?
    let totalDurationMin: Int?
    let stopsCount: Int?
    let routePolyline: String?
    let startedAt: String?
    let completedAt: String?
    let createdAt: String?
    let stops: [RouteStop]?

    enum CodingKeys: String, CodingKey {
        case id, status, stops
        case depotName = "depot_name"
        case depotLatitude = "depot_latitude"
        case depotLongitude = "depot_longitude"
        case totalDistanceKm = "total_distance_km"
        case totalDurationMin = "total_duration_min"
        case stopsCount = "stops_count"
        case routePolyline = "route_polyline"
        case startedAt = "started_at"
        case completedAt = "completed_at"
        case createdAt = "created_at"
    }
}

struct RouteStop: Codable, Identifiable, Sendable {
    let id: Int
    let stopOrder: Int
    let binSerial: String?
    let outlet: String?
    let brand: String?
    let address: String?
    let distanceKm: Double?
    let durationMin: Int?
    let status: String
    let latitude: Double
    let longitude: Double

    enum CodingKeys: String, CodingKey {
        case id, status, latitude, longitude, outlet, brand, address
        case stopOrder = "stop_order"
        case binSerial = "bin_serial"
        case distanceKm = "distance_km"
        case durationMin = "duration_min"
    }
}

struct RoutesResponse: Codable, Sendable {
    let routes: [CollectionRoute]
}

struct RouteDetailResponse: Codable, Sendable {
    let route: CollectionRoute
}
