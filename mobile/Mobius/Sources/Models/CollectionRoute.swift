import Foundation
import CoreLocation

// MARK: - Collection Route (matches new backend schema)

struct CollectionRoute: Codable, Identifiable, Sendable, Equatable {
    let id: Int
    var status: RouteStatus
    var depotLatitude: Double?
    var depotLongitude: Double?
    var depotName: String?
    var totalDistanceKm: Double?
    var totalDurationMin: Int?
    var routePolyline: String?
    var stops: [RouteStop]?
    var stopsCount: Int?
    var startedAt: Date?
    var completedAt: Date?
    var createdAt: Date?

    var depotCoordinate: CLLocationCoordinate2D? {
        guard let lat = depotLatitude, let lng = depotLongitude else { return nil }
        return CLLocationCoordinate2D(latitude: lat, longitude: lng)
    }

    var routeCoordinates: [CLLocationCoordinate2D] {
        guard let polyline = routePolyline else { return [] }
        return PolylineDecoder.decode(polyline)
    }

    var sortedStops: [RouteStop] {
        (stops ?? []).sorted { $0.stopOrder < $1.stopOrder }
    }

    var totalStops: Int {
        stopsCount ?? stops?.count ?? 0
    }

    var completedStopsCount: Int {
        (stops ?? []).filter { $0.status == .completed }.count
    }

    var progress: Double {
        let total = totalStops
        guard total > 0 else { return 0 }
        return Double(completedStopsCount) / Double(total)
    }

    var pendingStops: [RouteStop] {
        (stops ?? []).filter { $0.status == .pending }
    }

    var nextStop: RouteStop? {
        sortedStops.first { $0.status == .pending }
    }
}

// MARK: - Route Status

enum RouteStatus: String, Codable, Sendable {
    case pending
    case accepted
    case inProgress = "in_progress"
    case completed
    case rejected

    var label: String {
        switch self {
        case .pending: return "Pending"
        case .accepted: return "Accepted"
        case .inProgress: return "In Progress"
        case .completed: return "Completed"
        case .rejected: return "Rejected"
        }
    }

    var color: String {
        switch self {
        case .pending: return "orange"
        case .accepted: return "blue"
        case .inProgress: return "teal"
        case .completed: return "green"
        case .rejected: return "red"
        }
    }
}

// MARK: - Route Stop

struct RouteStop: Codable, Identifiable, Sendable, Equatable {
    let id: Int
    var stopOrder: Int
    var binSerial: String?
    var outlet: String?
    var brand: String?
    var address: String?
    var distanceKm: Double?
    var durationMin: Int?
    var status: StopStatus
    var eta: Date?
    var completedAt: Date?
    var latitude: Double?
    var longitude: Double?

    var coordinate: CLLocationCoordinate2D? {
        guard let lat = latitude, let lng = longitude else { return nil }
        return CLLocationCoordinate2D(latitude: lat, longitude: lng)
    }

    func distanceMeters(from location: CLLocationCoordinate2D) -> Double {
        guard let lat = latitude, let lng = longitude else { return .infinity }
        let earthRadius: Double = 6_371_000
        let dLat = (lat - location.latitude) * .pi / 180
        let dLng = (lng - location.longitude) * .pi / 180
        let a = sin(dLat / 2) * sin(dLat / 2)
            + cos(location.latitude * .pi / 180) * cos(lat * .pi / 180)
            * sin(dLng / 2) * sin(dLng / 2)
        let c = 2 * atan2(sqrt(a), sqrt(1 - a))
        return earthRadius * c
    }
}

enum StopStatus: String, Codable, Sendable {
    case pending
    case completed
    case skipped
}
