import SwiftUI
import MapKit

struct MapView: View {
    @State private var selectedOutlet: BinOutlet?
    @State private var cameraPosition: MapCameraPosition = .region(
        MKCoordinateRegion(
            center: CLLocationCoordinate2D(latitude: 5.4300, longitude: 100.3100),
            span: MKCoordinateSpan(latitudeDelta: 0.04, longitudeDelta: 0.04)
        )
    )

    private let outlets = BinOutlet.mockData

    var body: some View {
        ZStack(alignment: .bottom) {
            // Full-screen map
            Map(position: $cameraPosition, selection: $selectedOutlet) {
                ForEach(outlets) { outlet in
                    Annotation(outlet.name, coordinate: outlet.coordinate, anchor: .bottom) {
                        OutletPin(outlet: outlet, isSelected: selectedOutlet == outlet)
                    }
                    .tag(outlet)
                }

                UserAnnotation()
            }
            .mapStyle(.standard(elevation: .realistic))
            .mapControls {
                MapCompass()
                MapScaleView()
                MapUserLocationButton()
            }
            .ignoresSafeArea(edges: .bottom)

            // Floating outlet detail card
            if let outlet = selectedOutlet {
                outletDetailCard(outlet)
                    .padding(.horizontal, 16)
                    .padding(.bottom, 16)
                    .transition(.move(edge: .bottom).combined(with: .opacity))
                    .animation(.spring(response: 0.3), value: selectedOutlet)
            }
        }
        .navigationTitle("Find Bins")
        .navigationBarTitleDisplayMode(.inline)
        .onChange(of: selectedOutlet) { _, newOutlet in
            if let outlet = newOutlet {
                HapticManager.selection()
                withAnimation(.easeInOut(duration: 0.4)) {
                    cameraPosition = .region(
                        MKCoordinateRegion(
                            center: outlet.coordinate,
                            span: MKCoordinateSpan(latitudeDelta: 0.008, longitudeDelta: 0.008)
                        )
                    )
                }
            }
        }
    }

    // MARK: - Outlet Detail Card

    private func outletDetailCard(_ outlet: BinOutlet) -> some View {
        VStack(spacing: 12) {
            HStack(spacing: 14) {
                // Fill indicator
                ZStack {
                    Circle()
                        .stroke(.gray.opacity(0.2), lineWidth: 4)
                    Circle()
                        .trim(from: 0, to: Double(outlet.fillLevel) / 100.0)
                        .stroke(outlet.fillColor, style: StrokeStyle(lineWidth: 4, lineCap: .round))
                        .rotationEffect(.degrees(-90))
                    Text("\(outlet.fillLevel)%")
                        .font(.caption2.bold())
                }
                .frame(width: 44, height: 44)

                VStack(alignment: .leading, spacing: 3) {
                    Text(outlet.name)
                        .font(.headline)
                    HStack(spacing: 6) {
                        Label("\(outlet.binCount) bins", systemImage: "trash.fill")
                        Text("·")
                        Text(outlet.distance)
                    }
                    .font(.caption)
                    .foregroundStyle(.secondary)
                }

                Spacer()

                Button {
                    selectedOutlet = nil
                } label: {
                    Image(systemName: "xmark")
                        .font(.system(size: 11, weight: .bold))
                        .foregroundStyle(.secondary)
                        .frame(width: 28, height: 28)
                        .background(.quaternary, in: Circle())
                }
            }

            // Navigate button
            Button {
                let placemark = MKPlacemark(coordinate: outlet.coordinate)
                let item = MKMapItem(placemark: placemark)
                item.name = outlet.name
                item.openInMaps(launchOptions: [
                    MKLaunchOptionsDirectionsModeKey: MKLaunchOptionsDirectionsModeWalking,
                ])
            } label: {
                Label("Navigate", systemImage: "arrow.triangle.turn.up.right.diamond.fill")
                    .font(.subheadline.bold())
                    .frame(maxWidth: .infinity)
                    .frame(height: 44)
            }
            .buttonStyle(.borderedProminent)
            .tint(.blue)
            .buttonBorderShape(.capsule)
        }
        .padding(16)
        .background(.regularMaterial, in: RoundedRectangle(cornerRadius: 20))
        .shadow(color: .black.opacity(0.15), radius: 10, y: 5)
    }
}

// MARK: - Outlet Pin

private struct OutletPin: View {
    let outlet: BinOutlet
    let isSelected: Bool

    var body: some View {
        VStack(spacing: 0) {
            ZStack {
                Circle()
                    .fill(outlet.fillColor)
                    .frame(width: isSelected ? 40 : 32, height: isSelected ? 40 : 32)
                    .shadow(color: outlet.fillColor.opacity(0.4), radius: isSelected ? 6 : 3, y: 2)

                Image(systemName: "arrow.3.trianglepath")
                    .font(.system(size: isSelected ? 16 : 13, weight: .bold))
                    .foregroundStyle(.white)
            }
            .animation(.spring(response: 0.3), value: isSelected)

            // Pin pointer
            Path { path in
                path.move(to: CGPoint(x: 5, y: 6))
                path.addLine(to: CGPoint(x: 0, y: 0))
                path.addLine(to: CGPoint(x: 10, y: 0))
                path.closeSubpath()
            }
            .fill(outlet.fillColor)
            .frame(width: 10, height: 6)
            .offset(y: -1)
        }
    }
}

// MARK: - Bin Outlet Model

private struct BinOutlet: Identifiable, Hashable {
    let id: String
    let name: String
    let latitude: Double
    let longitude: Double
    let distance: String
    let fillLevel: Int
    let binCount: Int

    var coordinate: CLLocationCoordinate2D {
        CLLocationCoordinate2D(latitude: latitude, longitude: longitude)
    }

    var fillColor: Color {
        switch fillLevel {
        case 0..<40: .green
        case 40..<70: .orange
        default: .red
        }
    }

    static let mockData: [BinOutlet] = [
        BinOutlet(
            id: "starbucks_gurney",
            name: "Starbucks Gurney Plaza",
            latitude: 5.4370,
            longitude: 100.3105,
            distance: "0.3 km",
            fillLevel: 35,
            binCount: 2
        ),
        BinOutlet(
            id: "chagee_gurney",
            name: "CHAGEE Gurney Plaza",
            latitude: 5.4365,
            longitude: 100.3112,
            distance: "0.5 km",
            fillLevel: 62,
            binCount: 1
        ),
        BinOutlet(
            id: "zus_tanjung_bungah",
            name: "ZUS Coffee Tanjung Bungah",
            latitude: 5.4641,
            longitude: 100.2840,
            distance: "3.8 km",
            fillLevel: 90,
            binCount: 1
        ),
        BinOutlet(
            id: "mixue_tanjung",
            name: "Mixue Tanjung Tokong",
            latitude: 5.4450,
            longitude: 100.3020,
            distance: "1.2 km",
            fillLevel: 18,
            binCount: 1
        ),
        BinOutlet(
            id: "tealive_komtar",
            name: "Tealive Komtar",
            latitude: 5.4147,
            longitude: 100.3308,
            distance: "2.5 km",
            fillLevel: 85,
            binCount: 1
        ),
    ]
}

#Preview {
    NavigationStack {
        MapView()
    }
}
