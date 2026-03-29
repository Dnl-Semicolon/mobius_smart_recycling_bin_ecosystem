import Foundation
import CoreLocation

struct CollectionRoute: Codable, Identifiable, Sendable, Equatable {
    let id: Int
    var status: RouteStatus
    var zone: RouteZone?
    var stops: [RouteStop]
    var stopsCompleted: Int
    var stopsTotal: Int
    var totalDistanceKm: Double?
    var totalDurationMin: Int?
    var estimatedStartTime: Date?
    var startedAt: Date?
    var completedAt: Date?
    var createdAt: Date?
    var routeGeometry: String?
    var proximityThresholdMeters: Int?

    enum CodingKeys: String, CodingKey {
        case id, status, zone, stops
        case stopsCompleted = "stops_completed"
        case stopsTotal = "stops_total"
        case totalDistanceKm = "total_distance_km"
        case totalDurationMin = "total_duration_min"
        case estimatedStartTime = "estimated_start_time"
        case startedAt = "started_at"
        case completedAt = "completed_at"
        case createdAt = "created_at"
        case routeGeometry = "route_geometry"
        case proximityThresholdMeters = "proximity_threshold_meters"
    }

    /// The proximity threshold in meters, defaulting to 200 if not provided by the API.
    var proximityThreshold: Double {
        Double(proximityThresholdMeters ?? 200)
    }

    var routeCoordinates: [CLLocationCoordinate2D] {
        guard let geometry = routeGeometry else { return [] }
        return PolylineDecoder.decode(geometry)
    }

    var progress: Double {
        guard stopsTotal > 0 else { return 0 }
        return Double(stopsCompleted) / Double(stopsTotal)
    }

    var remainingStops: Int {
        stopsTotal - stopsCompleted
    }

    var pendingStops: [RouteStop] {
        stops.filter { $0.status == "pending" }
    }

    var nextStop: RouteStop? {
        stops.first { $0.status == "pending" }
    }
}

enum RouteStatus: String, Codable, Sendable {
    case pending = "pending"
    case accepted = "accepted"
    case active = "active"
    case completed = "completed"
    case cancelled = "cancelled"

    var label: String {
        rawValue.capitalized
    }

    var color: String {
        switch self {
        case .pending: "orange"
        case .accepted: "blue"
        case .active: "green"
        case .completed: "gray"
        case .cancelled: "red"
        }
    }
}

struct RouteZone: Codable, Sendable, Equatable {
    let id: Int
    var name: String
    var slug: String
    var region: String
    var depotName: String
    var depotLatitude: Double
    var depotLongitude: Double

    enum CodingKeys: String, CodingKey {
        case id, name, slug, region
        case depotName = "depot_name"
        case depotLatitude = "depot_latitude"
        case depotLongitude = "depot_longitude"
    }

    var depotCoordinate: CLLocationCoordinate2D {
        CLLocationCoordinate2D(latitude: depotLatitude, longitude: depotLongitude)
    }
}

struct RouteStop: Codable, Identifiable, Sendable, Equatable {
    var id: Int { order }

    var order: Int
    var binId: Int?
    var pickupRequestId: Int?
    var outletName: String
    var address: String
    var latitude: Double
    var longitude: Double
    var fillLevel: Int
    var serviceTimeSec: Int
    var etaMinutes: Int?
    var arrivalDistanceKm: Double?
    var status: String
    var completedAt: Date?
    var skipReason: String?

    enum CodingKeys: String, CodingKey {
        case order, address, latitude, longitude, status
        case binId = "bin_id"
        case pickupRequestId = "pickup_request_id"
        case outletName = "outlet_name"
        case fillLevel = "fill_level"
        case serviceTimeSec = "service_time_sec"
        case etaMinutes = "eta_minutes"
        case arrivalDistanceKm = "arrival_distance_km"
        case completedAt = "completed_at"
        case skipReason = "skip_reason"
    }

    var coordinate: CLLocationCoordinate2D {
        CLLocationCoordinate2D(latitude: latitude, longitude: longitude)
    }

    var isCompleted: Bool { status == "completed" }
    var isSkipped: Bool { status == "skipped" }
    var isPending: Bool { status == "pending" }

    /// Haversine distance from a given coordinate to this stop, in meters.
    func distanceMeters(from location: CLLocationCoordinate2D) -> Double {
        let earthRadius: Double = 6_371_000
        let dLat = (latitude - location.latitude) * .pi / 180
        let dLng = (longitude - location.longitude) * .pi / 180
        let a = sin(dLat / 2) * sin(dLat / 2)
            + cos(location.latitude * .pi / 180) * cos(latitude * .pi / 180)
            * sin(dLng / 2) * sin(dLng / 2)
        let c = 2 * atan2(sqrt(a), sqrt(1 - a))
        return earthRadius * c
    }

    /// Formatted distance string from a given coordinate.
    func distanceFormatted(from location: CLLocationCoordinate2D) -> String {
        let meters = distanceMeters(from: location)
        if meters < 1000 {
            return "\(Int(meters))m"
        }
        return String(format: "%.1fkm", meters / 1000)
    }
}

// MARK: - Mock Data

extension CollectionRoute {
    static let mockPending = CollectionRoute(
        id: 1,
        status: .pending,
        zone: RouteZone(
            id: 1,
            name: "George Town North",
            slug: "george-town-north",
            region: "Penang Island",
            depotName: "Mobius Recycling Hub Jelutong",
            depotLatitude: 5.3784,
            depotLongitude: 100.3354
        ),
        stops: RouteStop.mockStops,
        stopsCompleted: 0,
        stopsTotal: 5,
        totalDistanceKm: 38.6,
        totalDurationMin: 48,
        createdAt: Date(),
        proximityThresholdMeters: 200
    )

    static let mockActive = CollectionRoute(
        id: 2,
        status: .active,
        zone: RouteZone(
            id: 1,
            name: "George Town North",
            slug: "george-town-north",
            region: "Penang Island",
            depotName: "Mobius Recycling Hub Jelutong",
            depotLatitude: 5.3784,
            depotLongitude: 100.3354
        ),
        stops: [
            RouteStop(order: 1, binId: 4, outletName: "Tealive Komtar", address: "Komtar Walk, Jalan Penang, George Town", latitude: 5.4147, longitude: 100.3308, fillLevel: 85, serviceTimeSec: 300, etaMinutes: 15, arrivalDistanceKm: 6.8, status: "completed", completedAt: Date().addingTimeInterval(-600)),
            RouteStop(order: 2, binId: 1, outletName: "Starbucks Gurney Plaza", address: "Gurney Plaza, Persiaran Gurney, George Town", latitude: 5.4370, longitude: 100.3100, fillLevel: 95, serviceTimeSec: 300, etaMinutes: 20, arrivalDistanceKm: 13.7, status: "pending"),
            RouteStop(order: 3, binId: 3, outletName: "CHAGEE Gurney Plaza", address: "170-G-42, Gurney Plaza, George Town", latitude: 5.4362, longitude: 100.3093, fillLevel: 92, serviceTimeSec: 300, etaMinutes: 28, arrivalDistanceKm: 20.9, status: "pending"),
            RouteStop(order: 4, binId: 6, outletName: "ZUS Coffee Tanjung Bungah", address: "Tree Square, Jalan Sungai Kelian, Tanjung Bungah", latitude: 5.4641, longitude: 100.2840, fillLevel: 90, serviceTimeSec: 300, etaMinutes: 36, arrivalDistanceKm: 30.2, status: "pending"),
            RouteStop(order: 5, binId: 2, outletName: "Mixue Tanjung Tokong", address: "Jalan Tanjung Tokong, Tanjung Tokong", latitude: 5.4450, longitude: 100.3020, fillLevel: 88, serviceTimeSec: 300, etaMinutes: 42, arrivalDistanceKm: 35.8, status: "pending"),
        ],
        stopsCompleted: 1,
        stopsTotal: 5,
        totalDistanceKm: 38.6,
        totalDurationMin: 48,
        startedAt: Date().addingTimeInterval(-900),
        createdAt: Date().addingTimeInterval(-1800),
        proximityThresholdMeters: 200
    )
}

extension RouteStop {
    static let mockStops: [RouteStop] = [
        RouteStop(order: 1, binId: 4, outletName: "Tealive Komtar", address: "Komtar Walk, Jalan Penang, George Town", latitude: 5.4147, longitude: 100.3308, fillLevel: 85, serviceTimeSec: 300, etaMinutes: 15, arrivalDistanceKm: 6.8, status: "pending"),
        RouteStop(order: 2, binId: 1, outletName: "Starbucks Gurney Plaza", address: "Gurney Plaza, Persiaran Gurney, George Town", latitude: 5.4370, longitude: 100.3100, fillLevel: 95, serviceTimeSec: 300, etaMinutes: 20, arrivalDistanceKm: 13.7, status: "pending"),
        RouteStop(order: 3, binId: 3, outletName: "CHAGEE Gurney Plaza", address: "170-G-42, Gurney Plaza, George Town", latitude: 5.4362, longitude: 100.3093, fillLevel: 92, serviceTimeSec: 300, etaMinutes: 28, arrivalDistanceKm: 20.9, status: "pending"),
        RouteStop(order: 4, binId: 6, outletName: "ZUS Coffee Tanjung Bungah", address: "Tree Square, Jalan Sungai Kelian, Tanjung Bungah", latitude: 5.4641, longitude: 100.2840, fillLevel: 90, serviceTimeSec: 300, etaMinutes: 36, arrivalDistanceKm: 30.2, status: "pending"),
        RouteStop(order: 5, binId: 2, outletName: "Mixue Tanjung Tokong", address: "Jalan Tanjung Tokong, Tanjung Tokong", latitude: 5.4450, longitude: 100.3020, fillLevel: 88, serviceTimeSec: 300, etaMinutes: 42, arrivalDistanceKm: 35.8, status: "pending"),
    ]
}
