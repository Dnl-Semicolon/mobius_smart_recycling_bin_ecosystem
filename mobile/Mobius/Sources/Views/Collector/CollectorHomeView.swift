import SwiftUI

struct CollectorHomeView: View {
    @Environment(RoleManager.self) private var roleManager
    @Environment(CollectorService.self) private var collectorService
    @Environment(RouteService.self) private var routeService
    @Environment(NotificationManager.self) private var notificationManager

    var body: some View {
        List {
            // Route status card — Grab-style prominent card at top
            routeStatusSection

            // Quick Actions (Settings-app style — matches CustomerHomeView)
            Section("Overview") {
                settingsRow(
                    icon: "shippingbox.fill", iconColor: .orange,
                    title: "Available Pickups",
                    value: "\(collectorService.available.count)"
                )
                settingsRow(
                    icon: "truck.box.fill", iconColor: .blue,
                    title: "Active Pickups",
                    value: "\(collectorService.active.count)"
                )
                settingsRow(
                    icon: "checkmark.circle.fill", iconColor: .green,
                    title: "Completed",
                    value: "\(collectorService.stats?.totalCompleted ?? 0)"
                )
            }

            // Available pickups
            if !collectorService.available.isEmpty {
                Section("Available Pickups") {
                    ForEach(collectorService.available) { pickup in
                        pickupRow(pickup, showClaim: true)
                    }
                }
            }

            // Active pickups
            if !collectorService.active.isEmpty {
                Section("My Active Pickups") {
                    ForEach(collectorService.active) { pickup in
                        pickupRow(pickup, showComplete: true)
                    }
                }
            }

            // History
            if !collectorService.pickupHistory.isEmpty {
                Section("Recent History") {
                    ForEach(collectorService.pickupHistory.prefix(10)) { pickup in
                        pickupRow(pickup)
                    }
                }
            }
        }
        .refreshable {
            await refreshAll()
        }
        .navigationTitle("Home")
        .toolbar {
            ToolbarItem(placement: .topBarLeading) {
                RoleHeaderButton()
            }
            ToolbarItem(placement: .topBarTrailing) {
                NavigationLink {
                    NotificationsView()
                } label: {
                    Image(systemName: notificationManager.unreadCount > 0 ? "bell.badge.fill" : "bell.fill")
                        .symbolRenderingMode(.palette)
                        .foregroundStyle(notificationManager.unreadCount > 0 ? .red : .primary, .primary)
                }
            }
        }
        .navigationDestination(for: Int.self) { _ in
            CollectorRouteView()
        }
        .task {
            await refreshAll()
        }
    }

    private func refreshAll() async {
        async let p: () = collectorService.fetchPickups()
        async let s: () = collectorService.fetchStats()
        async let r: () = routeService.fetchRoutes()
        async let n: () = notificationManager.fetchUnreadCount()
        _ = await (p, s, r, n)
    }

    // MARK: - Route Status Card

    @ViewBuilder
    private var routeStatusSection: some View {
        let displayRoute = routeService.activeRoute
            ?? routeService.routes.first(where: { $0.status == .pending })

        Section {
            if routeService.isLoading && routeService.routes.isEmpty {
                HStack {
                    Spacer()
                    ProgressView()
                        .padding(.vertical, 4)
                    Spacer()
                }
            } else if let route = displayRoute {
                NavigationLink(value: route.id) {
                    routeStatusRow(route)
                }
            } else {
                HStack(spacing: 12) {
                    Image(systemName: "map.fill")
                        .font(.system(size: 16, weight: .medium))
                        .foregroundStyle(.white)
                        .frame(width: 32, height: 32)
                        .background(.gray, in: RoundedRectangle(cornerRadius: 7))
                    VStack(alignment: .leading, spacing: 2) {
                        Text("No Active Routes")
                            .font(.subheadline)
                        Text("Routes appear when bins need collection")
                            .font(.caption)
                            .foregroundStyle(.secondary)
                    }
                    Spacer()
                    Button {
                        Task { await routeService.fetchRoutes() }
                    } label: {
                        Image(systemName: "arrow.clockwise")
                            .font(.system(size: 13, weight: .semibold))
                    }
                    .buttonStyle(.bordered)
                    .buttonBorderShape(.capsule)
                    .controlSize(.small)
                }
            }
        } header: {
            Text("Route")
        }
    }

    private func routeStatusRow(_ route: CollectionRoute) -> some View {
        HStack(spacing: 14) {
            // Larger icon with accent — 40pt for active/pending, 32pt otherwise
            let isProminent = route.status == .inProgress || route.status == .pending
            Image(systemName: routeStatusIcon(route.status))
                .font(.system(size: isProminent ? 18 : 16, weight: .semibold))
                .foregroundStyle(.white)
                .frame(width: isProminent ? 40 : 32, height: isProminent ? 40 : 32)
                .background(routeAccentColor(route.status), in: RoundedRectangle(cornerRadius: isProminent ? 10 : 7))
                .shadow(color: routeAccentColor(route.status).opacity(isProminent ? 0.3 : 0), radius: 4, y: 2)

            VStack(alignment: .leading, spacing: 4) {
                Text(routeStatusTitle(route))
                    .font(.subheadline.bold())

                Group {
                    switch route.status {
                    case .pending:
                        Text("\(route.depotName ?? "Unknown") · \(route.totalStops) stops · \(route.totalDistanceKm.map { String(format: "%.1f km", $0) } ?? "--")")
                    case .accepted:
                        Text("\(route.depotName ?? "Unknown") · Tap to start navigation")
                    case .inProgress:
                        VStack(alignment: .leading, spacing: 4) {
                            HStack(spacing: 5) {
                                ForEach(route.stops) { stop in
                                    Circle()
                                        .fill(stop.isCompleted ? .green : stop.isSkipped ? .gray : stop.order == route.nextStop?.order ? .orange : Color(.tertiaryLabel))
                                        .frame(width: 8, height: 8)
                                }
                            }
                            if let next = route.nextStop {
                                Text("Next: \(next.outletName)")
                                    .lineLimit(1)
                            }
                        }
                    case .completed:
                        Text("\(route.completedStopsCount) stops completed")
                    case .cancelled:
                        Text("Route was cancelled")
                    }
                }
                .font(.caption)
                .foregroundStyle(.secondary)
            }

            Spacer(minLength: 0)

            // Chevron indicator for prominent states
            if route.status == .inProgress || route.status == .pending || route.status == .accepted {
                Image(systemName: "chevron.right")
                    .font(.system(size: 12, weight: .semibold))
                    .foregroundStyle(.tertiary)
            }
        }
        .padding(.vertical, 6)
        .listRowBackground(
            route.status == .inProgress
                ? Color.green.opacity(0.06)
                : route.status == .pending
                    ? Color.orange.opacity(0.06)
                    : nil
        )
    }

    private func routeStatusIcon(_ status: RouteStatus) -> String {
        switch status {
        case .pending: "map.fill"
        case .accepted: "checkmark.circle.fill"
        case .inProgress: "location.fill"
        case .completed: "flag.checkered"
        case .cancelled: "xmark.circle.fill"
        }
    }

    private func routeStatusTitle(_ route: CollectionRoute) -> String {
        switch route.status {
        case .pending: "New Route Available"
        case .accepted: "Route Ready"
        case .inProgress: "Route In Progress"
        case .completed: "Route Completed"
        case .cancelled: "Route Cancelled"
        }
    }

    private func routeAccentColor(_ status: RouteStatus) -> Color {
        switch status {
        case .pending: .orange
        case .accepted: .green
        case .inProgress: .green
        case .completed: .gray
        case .cancelled: .red
        }
    }

    // MARK: - Settings Row (Apple design language)

    private func settingsRow(icon: String, iconColor: Color, title: String, value: String) -> some View {
        HStack(spacing: 14) {
            Image(systemName: icon)
                .font(.system(size: 16, weight: .medium))
                .foregroundStyle(.white)
                .frame(width: 32, height: 32)
                .background(iconColor, in: RoundedRectangle(cornerRadius: 7))
            Text(title)
                .font(.subheadline)
            Spacer()
            Text(value)
                .font(.subheadline.bold())
                .foregroundStyle(.secondary)
        }
    }

    // MARK: - Pickup Row

    private func pickupRow(_ pickup: PickupItem, showClaim: Bool = false, showComplete: Bool = false) -> some View {
        VStack(alignment: .leading, spacing: 8) {
            HStack(spacing: 14) {
                Image(systemName: "shippingbox.fill")
                    .font(.system(size: 16, weight: .medium))
                    .foregroundStyle(.white)
                    .frame(width: 32, height: 32)
                    .background(statusColor(pickup.status), in: RoundedRectangle(cornerRadius: 7))
                VStack(alignment: .leading, spacing: 2) {
                    Text(pickup.binSerial)
                        .font(.subheadline.bold().monospaced())
                    Text(pickup.outletName)
                        .font(.caption)
                        .foregroundStyle(.secondary)
                }
                Spacer()
                Text(pickup.status.label)
                    .font(.caption.bold())
                    .padding(.horizontal, 8)
                    .padding(.vertical, 4)
                    .background(statusColor(pickup.status).opacity(0.15), in: Capsule())
                    .foregroundStyle(statusColor(pickup.status))
            }

            if showClaim {
                Button {
                    Task { await collectorService.claimPickup(pickup.id) }
                } label: {
                    Text("Claim Pickup")
                        .font(.subheadline.bold())
                        .frame(maxWidth: .infinity)
                }
                .buttonStyle(.borderedProminent)
                .tint(.orange)
                .controlSize(.small)
            }

            if showComplete {
                Button {
                    Task { await collectorService.completePickup(pickup.id) }
                } label: {
                    Text("Mark Complete")
                        .font(.subheadline.bold())
                        .frame(maxWidth: .infinity)
                }
                .buttonStyle(.borderedProminent)
                .tint(.green)
                .controlSize(.small)
            }
        }
        .padding(.vertical, 4)
    }

    private func statusColor(_ status: PickupStatus) -> Color {
        switch status {
        case .pending: .orange
        case .claimed: .blue
        case .completed: .green
        case .cancelled: .gray
        }
    }
}

#Preview {
    NavigationStack {
        CollectorHomeView()
    }
    .environment(AuthManager.mockAuthenticated())
    .environment(RoleManager.mockMultiRole())
    .environment(CollectorService.mockWithData())
    .environment(RouteService.mockWithPendingRoute())
}

#Preview("With Active Route") {
    NavigationStack {
        CollectorHomeView()
    }
    .environment(AuthManager.mockAuthenticated())
    .environment(RoleManager.mockMultiRole())
    .environment(CollectorService.mockWithData())
    .environment(RouteService.mockWithActiveRoute())
}
