import CoreLocation
import SwiftUI

struct ActiveRouteView: View {
    var routeVM: RouteViewModel
    let route: CollectionRoute
    @State private var locationManager = LocationManager()
    @State private var skipReason = ""
    @State private var showingSkipSheet = false
    @State private var skipStopOrder: Int?
    @Environment(\.dismiss) private var dismiss

    private var activeRoute: CollectionRoute {
        routeVM.selectedRoute ?? route
    }

    private var stops: [RouteStop] {
        activeRoute.stops ?? []
    }

    private var currentStop: RouteStop? {
        stops.first { $0.status == "pending" }
    }

    private var completedCount: Int {
        stops.filter { $0.status == "completed" || $0.status == "skipped" }.count
    }

    private var allStopsDone: Bool {
        !stops.isEmpty && stops.allSatisfy { $0.status != "pending" }
    }

    var body: some View {
        ZStack(alignment: .bottom) {
            // Map fills the screen
            RouteMapView(
                route: activeRoute,
                userLocation: locationManager.currentLocation,
                highlightStopOrder: currentStop?.stopOrder
            )
            .ignoresSafeArea(edges: .top)

            // Bottom panel
            VStack(spacing: 0) {
                // Progress bar
                HStack {
                    Text("Stop \(min(completedCount + 1, stops.count)) of \(stops.count)")
                        .font(.subheadline.bold())
                    Spacer()
                    ProgressView(value: Double(completedCount), total: Double(max(stops.count, 1)))
                        .frame(width: 100)
                        .tint(Color.mobiusTeal)
                }
                .padding(.horizontal)
                .padding(.vertical, 12)
                .background(.ultraThinMaterial)

                if allStopsDone {
                    completeRoutePanel
                } else if let stop = currentStop {
                    currentStopPanel(stop)
                }
            }
        }
        .navigationTitle("Active Route")
        .navigationBarTitleDisplayMode(.inline)
        .toolbarBackground(.hidden, for: .navigationBar)
        .sheet(isPresented: $showingSkipSheet) {
            skipSheet
        }
        .onAppear {
            locationManager.requestPermission()
            locationManager.startTracking()
        }
        .onDisappear {
            locationManager.stopTracking()
        }
    }

    // MARK: - Complete Route Panel

    private var completeRoutePanel: some View {
        VStack(spacing: 12) {
            Image(systemName: "checkmark.circle.fill")
                .font(.system(size: 48))
                .foregroundStyle(.green)

            Text("All Stops Complete!")
                .font(.title3.bold())

            Button {
                Task {
                    await routeVM.completeRoute(id: activeRoute.id)
                    dismiss()
                }
            } label: {
                Text("Complete Route")
                    .font(.headline)
                    .frame(maxWidth: .infinity)
                    .frame(height: 50)
            }
            .buttonStyle(.borderedProminent)
            .tint(.green)
        }
        .padding()
        .background(.regularMaterial, in: RoundedRectangle(cornerRadius: 20))
        .padding()
    }

    // MARK: - Current Stop Panel

    private func currentStopPanel(_ stop: RouteStop) -> some View {
        VStack(alignment: .leading, spacing: 12) {
            HStack {
                ZStack {
                    Circle()
                        .fill(Color.mobiusTeal)
                        .frame(width: 36, height: 36)
                    Text("\(stop.stopOrder)")
                        .font(.callout.bold())
                        .foregroundStyle(.white)
                }

                VStack(alignment: .leading, spacing: 2) {
                    Text(stop.outlet ?? "Stop \(stop.stopOrder)")
                        .font(.headline)
                    Text(stop.binSerial ?? "")
                        .font(.caption)
                        .foregroundStyle(.secondary)
                }

                Spacer()

                if let brand = stop.brand {
                    Text(brand)
                        .font(.caption.bold())
                        .padding(.horizontal, 8)
                        .padding(.vertical, 4)
                        .background(Color.mobiusTeal.opacity(0.15), in: Capsule())
                        .foregroundStyle(Color.mobiusTeal)
                }
            }

            if let address = stop.address {
                Text(address)
                    .font(.caption)
                    .foregroundStyle(.secondary)
            }

            HStack(spacing: 12) {
                Button {
                    skipStopOrder = stop.stopOrder
                    showingSkipSheet = true
                } label: {
                    Text("Skip")
                        .font(.headline)
                        .frame(maxWidth: .infinity)
                        .frame(height: 50)
                }
                .buttonStyle(.bordered)
                .tint(.orange)

                Button {
                    Task {
                        let lat = locationManager.currentLocation?.latitude ?? stop.latitude
                        let lng = locationManager.currentLocation?.longitude ?? stop.longitude
                        await routeVM.completeStop(
                            routeId: activeRoute.id,
                            stopOrder: stop.stopOrder,
                            latitude: lat,
                            longitude: lng
                        )
                    }
                } label: {
                    Label("Complete Stop", systemImage: "checkmark")
                        .font(.headline)
                        .frame(maxWidth: .infinity)
                        .frame(height: 50)
                }
                .buttonStyle(.borderedProminent)
                .tint(Color.mobiusTeal)
            }
        }
        .padding()
        .background(.regularMaterial, in: RoundedRectangle(cornerRadius: 20))
        .padding()
    }

    // MARK: - Skip Sheet

    private var skipSheet: some View {
        NavigationStack {
            Form {
                Section("Reason for skipping") {
                    TextField("Enter reason...", text: $skipReason, axis: .vertical)
                        .lineLimit(3...6)
                }
            }
            .navigationTitle("Skip Stop")
            .navigationBarTitleDisplayMode(.inline)
            .toolbar {
                ToolbarItem(placement: .cancellationAction) {
                    Button("Cancel") {
                        showingSkipSheet = false
                        skipReason = ""
                    }
                }
                ToolbarItem(placement: .confirmationAction) {
                    Button("Skip") {
                        if let order = skipStopOrder {
                            Task {
                                await routeVM.skipStop(
                                    routeId: activeRoute.id,
                                    stopOrder: order,
                                    reason: skipReason
                                )
                                showingSkipSheet = false
                                skipReason = ""
                            }
                        }
                    }
                    .disabled(skipReason.trimmingCharacters(in: .whitespaces).isEmpty)
                }
            }
        }
        .presentationDetents([.medium])
    }
}
