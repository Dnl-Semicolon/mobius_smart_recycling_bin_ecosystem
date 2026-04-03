import CoreLocation

enum PolylineDecoder {
    static func decode(_ encoded: String) -> [CLLocationCoordinate2D] {
        // Guard against placeholder/invalid strings
        guard !encoded.isEmpty,
              encoded.allSatisfy({ $0.asciiValue != nil && $0.asciiValue! >= 63 })
        else { return [] }

        var coordinates: [CLLocationCoordinate2D] = []
        var index = encoded.startIndex
        var lat: Int32 = 0
        var lng: Int32 = 0

        while index < encoded.endIndex {
            var shift: Int32 = 0
            var result: Int32 = 0
            var byte: Int32

            repeat {
                guard index < encoded.endIndex,
                      let ascii = encoded[index].asciiValue,
                      ascii >= 63
                else { return coordinates }

                byte = Int32(ascii - 63)
                index = encoded.index(after: index)
                result |= (byte & 0x1F) << shift
                shift += 5
            } while byte >= 0x20

            lat += (result & 1) != 0 ? ~(result >> 1) : (result >> 1)

            shift = 0
            result = 0

            repeat {
                guard index < encoded.endIndex,
                      let ascii = encoded[index].asciiValue,
                      ascii >= 63
                else { return coordinates }

                byte = Int32(ascii - 63)
                index = encoded.index(after: index)
                result |= (byte & 0x1F) << shift
                shift += 5
            } while byte >= 0x20

            lng += (result & 1) != 0 ? ~(result >> 1) : (result >> 1)

            coordinates.append(CLLocationCoordinate2D(
                latitude: Double(lat) / 1e5,
                longitude: Double(lng) / 1e5
            ))
        }

        return coordinates
    }
}
