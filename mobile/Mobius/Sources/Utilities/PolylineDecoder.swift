import CoreLocation
import Foundation

enum PolylineDecoder: Sendable {

    /// Decodes a Google-encoded polyline string into an array of coordinates.
    ///
    /// Uses the standard Google encoding algorithm with 5-digit precision (1e-5).
    /// Reference: https://developers.google.com/maps/documentation/utilities/polylinealgorithm
    static func decode(_ encodedString: String) -> [CLLocationCoordinate2D] {
        guard !encodedString.isEmpty else { return [] }

        var coordinates: [CLLocationCoordinate2D] = []
        let bytes = Array(encodedString.utf8)
        let length = bytes.count
        var index = 0
        var latitude: Int32 = 0
        var longitude: Int32 = 0

        while index < length {
            // Decode latitude delta
            var result: Int32 = 0
            var shift: Int32 = 0
            var byte: Int32

            repeat {
                guard index < length else { return coordinates }
                byte = Int32(bytes[index]) - 63
                index += 1
                result |= (byte & 0x1F) << shift
                shift += 5
            } while byte >= 0x20

            let latDelta = (result & 1) != 0 ? ~(result >> 1) : (result >> 1)
            latitude += latDelta

            // Decode longitude delta
            result = 0
            shift = 0

            repeat {
                guard index < length else { return coordinates }
                byte = Int32(bytes[index]) - 63
                index += 1
                result |= (byte & 0x1F) << shift
                shift += 5
            } while byte >= 0x20

            let lngDelta = (result & 1) != 0 ? ~(result >> 1) : (result >> 1)
            longitude += lngDelta

            let coordinate = CLLocationCoordinate2D(
                latitude: Double(latitude) / 1e5,
                longitude: Double(longitude) / 1e5
            )
            coordinates.append(coordinate)
        }

        return coordinates
    }
}
