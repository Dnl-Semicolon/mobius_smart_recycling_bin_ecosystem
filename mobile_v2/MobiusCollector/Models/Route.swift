import Foundation

// MARK: - Flexible decoding (Laravel decimal casts serialize as strings)

extension KeyedDecodingContainer {
    func flexDouble(forKey key: Key) -> Double? {
        if let val = try? decodeIfPresent(Double.self, forKey: key) { return val }
        if let str = try? decodeIfPresent(String.self, forKey: key) { return Double(str) }
        return nil
    }

    func flexDoubleRequired(forKey key: Key, fallback: Double = 0) -> Double {
        if let val = try? decode(Double.self, forKey: key) { return val }
        if let str = try? decode(String.self, forKey: key), let val = Double(str) { return val }
        return fallback
    }

    func flexInt(forKey key: Key) -> Int? {
        if let val = try? decodeIfPresent(Int.self, forKey: key) { return val }
        if let str = try? decodeIfPresent(String.self, forKey: key) { return Int(str) }
        return nil
    }
}

// MARK: - Models

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

    init(from decoder: Decoder) throws {
        let c = try decoder.container(keyedBy: CodingKeys.self)
        id = try c.decode(Int.self, forKey: .id)
        status = try c.decode(String.self, forKey: .status)
        depotName = try c.decodeIfPresent(String.self, forKey: .depotName)
        depotLatitude = c.flexDouble(forKey: .depotLatitude)
        depotLongitude = c.flexDouble(forKey: .depotLongitude)
        totalDistanceKm = c.flexDouble(forKey: .totalDistanceKm)
        totalDurationMin = c.flexInt(forKey: .totalDurationMin)
        stopsCount = c.flexInt(forKey: .stopsCount)
        routePolyline = try c.decodeIfPresent(String.self, forKey: .routePolyline)
        startedAt = try c.decodeIfPresent(String.self, forKey: .startedAt)
        completedAt = try c.decodeIfPresent(String.self, forKey: .completedAt)
        createdAt = try c.decodeIfPresent(String.self, forKey: .createdAt)
        stops = try c.decodeIfPresent([RouteStop].self, forKey: .stops)
    }

    func encode(to encoder: Encoder) throws {
        var c = encoder.container(keyedBy: CodingKeys.self)
        try c.encode(id, forKey: .id)
        try c.encode(status, forKey: .status)
        try c.encodeIfPresent(depotName, forKey: .depotName)
        try c.encodeIfPresent(depotLatitude, forKey: .depotLatitude)
        try c.encodeIfPresent(depotLongitude, forKey: .depotLongitude)
        try c.encodeIfPresent(totalDistanceKm, forKey: .totalDistanceKm)
        try c.encodeIfPresent(totalDurationMin, forKey: .totalDurationMin)
        try c.encodeIfPresent(stopsCount, forKey: .stopsCount)
        try c.encodeIfPresent(routePolyline, forKey: .routePolyline)
        try c.encodeIfPresent(startedAt, forKey: .startedAt)
        try c.encodeIfPresent(completedAt, forKey: .completedAt)
        try c.encodeIfPresent(createdAt, forKey: .createdAt)
        try c.encodeIfPresent(stops, forKey: .stops)
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

    init(from decoder: Decoder) throws {
        let c = try decoder.container(keyedBy: CodingKeys.self)
        id = try c.decode(Int.self, forKey: .id)
        stopOrder = try c.decode(Int.self, forKey: .stopOrder)
        binSerial = try c.decodeIfPresent(String.self, forKey: .binSerial)
        outlet = try c.decodeIfPresent(String.self, forKey: .outlet)
        brand = try c.decodeIfPresent(String.self, forKey: .brand)
        address = try c.decodeIfPresent(String.self, forKey: .address)
        distanceKm = c.flexDouble(forKey: .distanceKm)
        durationMin = c.flexInt(forKey: .durationMin)
        status = try c.decode(String.self, forKey: .status)
        latitude = c.flexDoubleRequired(forKey: .latitude)
        longitude = c.flexDoubleRequired(forKey: .longitude)
    }

    func encode(to encoder: Encoder) throws {
        var c = encoder.container(keyedBy: CodingKeys.self)
        try c.encode(id, forKey: .id)
        try c.encode(stopOrder, forKey: .stopOrder)
        try c.encodeIfPresent(binSerial, forKey: .binSerial)
        try c.encodeIfPresent(outlet, forKey: .outlet)
        try c.encodeIfPresent(brand, forKey: .brand)
        try c.encodeIfPresent(address, forKey: .address)
        try c.encodeIfPresent(distanceKm, forKey: .distanceKm)
        try c.encodeIfPresent(durationMin, forKey: .durationMin)
        try c.encode(status, forKey: .status)
        try c.encode(latitude, forKey: .latitude)
        try c.encode(longitude, forKey: .longitude)
    }
}

struct RoutesResponse: Codable, Sendable {
    let routes: [CollectionRoute]
}

struct RouteDetailResponse: Codable, Sendable {
    let route: CollectionRoute
}
