import MapKit
import SwiftUI

struct RouteMapView: View {
    let route: CollectionRoute
    var userLocation: CLLocationCoordinate2D? = nil
    var highlightStopOrder: Int? = nil

    @State private var position: MapCameraPosition = .automatic

    private var stops: [RouteStop] { route.stops ?? [] }

    private var polylineCoordinates: [CLLocationCoordinate2D] {
        guard let encoded = route.routePolyline, !encoded.isEmpty else { return [] }
        return PolylineDecoder.decode(encoded)
    }

    private var depotCoordinate: CLLocationCoordinate2D? {
        guard let lat = route.depotLatitude, let lng = route.depotLongitude else { return nil }
        return CLLocationCoordinate2D(latitude: lat, longitude: lng)
    }

    var body: some View {
        Map(position: $position) {
            // Depot
            if let depot = depotCoordinate {
                Annotation("Depot", coordinate: depot) {
                    ZStack {
                        Circle()
                            .fill(.white)
                            .frame(width: 34, height: 34)
                            .shadow(radius: 2)
                        Image(systemName: "building.2.fill")
                            .font(.system(size: 16))
                            .foregroundStyle(.indigo)
                    }
                }
            }

            // Stops
            ForEach(stops) { stop in
                let coord = CLLocationCoordinate2D(latitude: stop.latitude, longitude: stop.longitude)
                Annotation(stop.outlet ?? "Stop \(stop.stopOrder)", coordinate: coord) {
                    stopMarker(for: stop)
                }
            }

            // User location
            if let loc = userLocation {
                Annotation("You", coordinate: loc) {
                    ZStack {
                        Circle()
                            .fill(.blue.opacity(0.2))
                            .frame(width: 32, height: 32)
                        Circle()
                            .fill(.blue)
                            .frame(width: 14, height: 14)
                        Circle()
                            .stroke(.white, lineWidth: 2)
                            .frame(width: 14, height: 14)
                    }
                }
            }

            // Polyline
            if !polylineCoordinates.isEmpty {
                MapPolyline(coordinates: polylineCoordinates)
                    .stroke(Color.mobiusTeal, lineWidth: 4)
            }
        }
        .mapStyle(.standard)
        .mapControls {
            MapCompass()
            MapScaleView()
        }
    }

    @ViewBuilder
    private func stopMarker(for stop: RouteStop) -> some View {
        let isHighlighted = highlightStopOrder == stop.stopOrder
        let size: CGFloat = isHighlighted ? 40 : 32

        ZStack {
            Circle()
                .fill(markerColor(for: stop))
                .frame(width: size, height: size)
                .shadow(radius: isHighlighted ? 4 : 2)

            switch stop.status {
            case "completed":
                Image(systemName: "checkmark")
                    .font(.system(size: 14, weight: .bold))
                    .foregroundStyle(.white)
            case "skipped":
                Image(systemName: "forward.fill")
                    .font(.system(size: 12, weight: .bold))
                    .foregroundStyle(.white)
            default:
                Text("\(stop.stopOrder)")
                    .font(.system(size: 14, weight: .bold))
                    .foregroundStyle(.white)
            }
        }
    }

    private func markerColor(for stop: RouteStop) -> Color {
        switch stop.status {
        case "completed": .green
        case "skipped": .orange
        default:
            highlightStopOrder == stop.stopOrder ? Color.mobiusTeal : Color.mobiusTeal.opacity(0.7)
        }
    }
}
