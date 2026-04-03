import SwiftUI

struct RouteDetailView: View {
    let routeId: Int
    var routeVM: RouteViewModel
    @State private var showingActiveRoute = false
    @State private var showingRejectConfirm = false

    private var route: CollectionRoute? { routeVM.selectedRoute }

    var body: some View {
        Group {
            if let route {
                routeContent(route)
            } else if routeVM.isLoading {
                ProgressView("Loading route...")
            } else {
                ContentUnavailableView("Route Not Found", systemImage: "map")
            }
        }
        .navigationTitle("Route #\(routeId)")
        .navigationBarTitleDisplayMode(.inline)
        .navigationDestination(isPresented: $showingActiveRoute) {
            if let route {
                ActiveRouteView(routeVM: routeVM, route: route)
            }
        }
        .confirmationDialog("Reject Route?", isPresented: $showingRejectConfirm) {
            Button("Reject", role: .destructive) {
                Task { await routeVM.rejectRoute(id: routeId) }
            }
        } message: {
            Text("Are you sure you want to reject this route?")
        }
        .task {
            await routeVM.fetchRouteDetail(id: routeId)
        }
    }

    @ViewBuilder
    private func routeContent(_ route: CollectionRoute) -> some View {
        VStack(spacing: 0) {
            // Map — top portion
            RouteMapView(route: route)
                .frame(height: UIScreen.main.bounds.height * 0.45)

            // Scrollable details
            ScrollView {
                VStack(alignment: .leading, spacing: 16) {
                    // Header
                    HStack {
                        VStack(alignment: .leading, spacing: 4) {
                            if let depot = route.depotName {
                                Text(depot)
                                    .font(.title2.bold())
                            }
                            StatusBadge(status: route.status)
                        }
                        Spacer()
                    }

                    // Stats row
                    HStack(spacing: 24) {
                        if let dist = route.totalDistanceKm {
                            Label(String(format: "%.1f km", dist), systemImage: "road.lanes")
                        }
                        if let dur = route.totalDurationMin {
                            Label("\(dur) min", systemImage: "clock")
                        }
                        if let stops = route.stops {
                            Label("\(stops.count) stops", systemImage: "mappin.and.ellipse")
                        }
                    }
                    .font(.subheadline)
                    .foregroundStyle(.secondary)

                    Divider()

                    // Stops
                    if let stops = route.stops, !stops.isEmpty {
                        Text("Stops")
                            .font(.headline)

                        ForEach(stops) { stop in
                            StopRow(stop: stop)
                        }
                    }

                    // Actions
                    actionButtons(for: route)
                        .padding(.top, 8)
                        .padding(.bottom, 24)
                }
                .padding()
            }
        }
    }

    @ViewBuilder
    private func actionButtons(for route: CollectionRoute) -> some View {
        switch route.status {
        case "pending":
            HStack(spacing: 12) {
                Button {
                    showingRejectConfirm = true
                } label: {
                    Text("Reject")
                        .font(.headline)
                        .frame(maxWidth: .infinity)
                        .frame(height: 50)
                }
                .buttonStyle(.bordered)
                .tint(.red)

                Button {
                    Task { await routeVM.acceptRoute(id: routeId) }
                } label: {
                    Text("Accept")
                        .font(.headline)
                        .frame(maxWidth: .infinity)
                        .frame(height: 50)
                }
                .buttonStyle(.borderedProminent)
                .tint(Color.mobiusTeal)
            }

        case "accepted":
            Button {
                Task {
                    await routeVM.startRoute(id: routeId)
                    showingActiveRoute = true
                }
            } label: {
                Label("Start Navigation", systemImage: "location.fill")
                    .font(.headline)
                    .frame(maxWidth: .infinity)
                    .frame(height: 50)
            }
            .buttonStyle(.borderedProminent)
            .tint(Color.mobiusTeal)

        case "in_progress":
            Button {
                showingActiveRoute = true
            } label: {
                Label("Continue Navigation", systemImage: "location.fill")
                    .font(.headline)
                    .frame(maxWidth: .infinity)
                    .frame(height: 50)
            }
            .buttonStyle(.borderedProminent)
            .tint(Color.mobiusTeal)

        case "completed":
            HStack {
                Image(systemName: "checkmark.circle.fill")
                    .foregroundStyle(.green)
                Text("Route Completed")
                    .font(.headline)
            }
            .frame(maxWidth: .infinity)
            .padding()
            .background(Color.green.opacity(0.1), in: RoundedRectangle(cornerRadius: 12))

        default:
            EmptyView()
        }
    }
}

// MARK: - Stop Row

struct StopRow: View {
    let stop: RouteStop

    private var statusIcon: String {
        switch stop.status {
        case "completed": "checkmark.circle.fill"
        case "skipped": "forward.circle.fill"
        default: "circle"
        }
    }

    private var statusColor: Color {
        switch stop.status {
        case "completed": .green
        case "skipped": .orange
        default: .mobiusTeal
        }
    }

    var body: some View {
        HStack(alignment: .top, spacing: 12) {
            ZStack {
                Circle()
                    .fill(statusColor.opacity(0.15))
                    .frame(width: 36, height: 36)

                if stop.status == "pending" {
                    Text("\(stop.stopOrder)")
                        .font(.callout.bold())
                        .foregroundStyle(statusColor)
                } else {
                    Image(systemName: statusIcon)
                        .foregroundStyle(statusColor)
                }
            }

            VStack(alignment: .leading, spacing: 4) {
                if let outlet = stop.outlet {
                    Text(outlet)
                        .font(.subheadline.bold())
                }

                if let serial = stop.binSerial {
                    Text(serial)
                        .font(.caption)
                        .foregroundStyle(.secondary)
                }

                if let brand = stop.brand {
                    Text(brand)
                        .font(.caption)
                        .padding(.horizontal, 6)
                        .padding(.vertical, 2)
                        .background(Color.mobiusTeal.opacity(0.1), in: Capsule())
                        .foregroundStyle(Color.mobiusTeal)
                }

                if let address = stop.address {
                    Text(address)
                        .font(.caption)
                        .foregroundStyle(.secondary)
                        .lineLimit(2)
                }

                HStack(spacing: 12) {
                    if let dist = stop.distanceKm {
                        Text(String(format: "%.1f km", dist))
                    }
                    if let dur = stop.durationMin {
                        Text("\(dur) min")
                    }
                }
                .font(.caption2)
                .foregroundStyle(.tertiary)
            }

            Spacer()
        }
        .padding(.vertical, 6)
    }
}
