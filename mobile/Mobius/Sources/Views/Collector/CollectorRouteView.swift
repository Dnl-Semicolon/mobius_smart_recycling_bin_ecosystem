import MapKit
import SwiftUI

// MARK: - Main View

struct CollectorRouteView: View {
    @Environment(RoleManager.self) private var roleManager
    @Environment(RouteService.self) private var routeService
    @Environment(\.dismiss) private var dismiss

    @State private var locationManager = LocationManager()
    @State private var cameraPosition: MapCameraPosition = .automatic
    @State private var mapSelection: RouteStop?
    @State private var showSkipAlert = false
    @State private var skipReason = ""
    @State private var stopToSkip: RouteStop?
    @State private var showCompletion = false
    @State private var isFollowingUser = false

    // MKDirections state
    @State private var directionsCoordinates: [CLLocationCoordinate2D] = []
    @State private var directionsETA: TimeInterval?
    @State private var directionsDistance: CLLocationDistance?
    @State private var lastDirectionsTarget: Int?
    @State private var lastDirectionsLocation: CLLocationCoordinate2D?
    @State private var isRequestingDirections = false
    @State private var showExitAlert = false
    @State private var arrivedPulse = false

    private var route: CollectionRoute? {
        routeService.inProgressRoute ?? routeService.routes.first
    }

    var body: some View {
        ZStack {
            routeMap
                .ignoresSafeArea(edges: .top)

            if let route {
                floatingUI(route)
            } else {
                emptyOverlay
            }
        }
        .alert("Skip Stop", isPresented: $showSkipAlert) {
            TextField("Reason (e.g. outlet closed)", text: $skipReason)
            Button("Skip", role: .destructive) { performSkip() }
            Button("Cancel", role: .cancel) {
                skipReason = ""
                stopToSkip = nil
            }
        } message: {
            Text("This stop will be skipped and requeued for the next route.")
        }
        .sheet(isPresented: $showCompletion) {
            if let route {
                RouteCompletionSheet(route: route) {
                    showCompletion = false
                    Task {
                        await routeService.fetchRoutes()
                        dismiss()
                    }
                }
            }
        }
        .toolbar(.hidden, for: .tabBar)
        .navigationBarBackButtonHidden(true)
        .toolbar {
            ToolbarItem(placement: .topBarLeading) {
                if route?.status != .inProgress {
                    Button {
                        dismiss()
                    } label: {
                        HStack(spacing: 4) {
                            Image(systemName: "chevron.left")
                                .font(.system(size: 14, weight: .semibold))
                            Text("Home")
                        }
                    }
                }
            }
        }
        .task {
            locationManager.requestPermission()
            await routeService.fetchRoutes()
        }
        .onChange(of: route?.status) { _, newStatus in
            if newStatus == .completed { showCompletion = true }
            if newStatus == .inProgress {
                locationManager.startTracking()
                withAnimation(.easeInOut(duration: 0.6)) {
                    cameraPosition = .userLocation(followsHeading: true, fallback: .automatic)
                    isFollowingUser = true
                }
            }
        }
        .onChange(of: route?.nextStop?.order) { oldOrder, newOrder in
            if route?.status == .inProgress, newOrder != nil, newOrder != oldOrder {
                clearDirections()
                requestDirectionsIfNeeded()
            }
        }
        .onChange(of: locationManager.userLocation?.latitude) { _, _ in
            if route?.status == .inProgress {
                requestDirectionsIfNeeded()
            }
        }
    }
}

// MARK: - Map

extension CollectorRouteView {
    private var routeMap: some View {
        Map(position: $cameraPosition, selection: $mapSelection) {
            // VROOM planned route — thin gray dashed context line
            if let route, !route.routeCoordinates.isEmpty {
                MapPolyline(coordinates: route.routeCoordinates)
                    .stroke(.gray.opacity(0.4), style: StrokeStyle(lineWidth: 4, lineCap: .round, lineJoin: .round, dash: [8, 6]))
            }

            // MKDirections route — thick bold blue (9pt)
            if route?.status == .inProgress, !directionsCoordinates.isEmpty {
                MapPolyline(coordinates: directionsCoordinates)
                    .stroke(.blue, style: StrokeStyle(lineWidth: 9, lineCap: .round, lineJoin: .round))
            }

            // VROOM solid overlay (non-active or no directions yet) — 6pt
            if route?.status != .inProgress || directionsCoordinates.isEmpty {
                if let route, !route.routeCoordinates.isEmpty {
                    MapPolyline(coordinates: route.routeCoordinates)
                        .stroke(.blue.opacity(0.7), style: StrokeStyle(lineWidth: 6, lineCap: .round, lineJoin: .round))
                }
            }

            // Depot
            if let depotCoord = route?.depotCoordinate {
                Annotation("Depot", coordinate: depotCoord, anchor: .bottom) {
                    DepotPin()
                }
            }

            // Stops
            if let stops = route?.stops {
                ForEach(stops) { stop in
                    Annotation((stop.outlet ?? "Unknown"), coordinate: (stop.coordinate ?? CLLocationCoordinate2D()), anchor: .bottom) {
                        StopPin(stop: stop, isNext: stop.stopOrder == route?.nextStop?.order)
                    }
                    .tag(stop)
                }
            }

            UserAnnotation()
        }
        .mapStyle(.standard(elevation: .realistic))
        .mapControls {
            MapCompass()
            MapScaleView()
        }
        .safeAreaPadding(.top, 50)
        .onMapCameraChange(frequency: .onEnd) { _ in
            // Detect user panning during active nav → show re-centre button
            if route?.status == .inProgress && isFollowingUser {
                isFollowingUser = false
            }
        }
    }
}

// MARK: - MKDirections

extension CollectorRouteView {
    private func requestDirectionsIfNeeded() {
        guard let route, route.status == .inProgress,
              let nextStop = route.nextStop,
              let userLoc = locationManager.userLocation,
              !isRequestingDirections else { return }

        if lastDirectionsTarget == nextStop.order,
           let lastLoc = lastDirectionsLocation {
            let movedMeters = RouteStop.haversineDistance(from: userLoc, to: lastLoc)
            if movedMeters < 300 { return }
        }

        isRequestingDirections = true
        lastDirectionsTarget = nextStop.order
        lastDirectionsLocation = userLoc

        Task {
            await fetchDirections(from: userLoc, to: nextStop.coordinate)
            isRequestingDirections = false
        }
    }

    private func fetchDirections(from source: CLLocationCoordinate2D, to destination: CLLocationCoordinate2D) async {
        let request = MKDirections.Request()
        request.source = MKMapItem(placemark: MKPlacemark(coordinate: source))
        request.destination = MKMapItem(placemark: MKPlacemark(coordinate: destination))
        request.transportType = .automobile

        let directions = MKDirections(request: request)
        do {
            let response = try await directions.calculate()
            if let mkRoute = response.routes.first {
                let pointCount = mkRoute.polyline.pointCount
                let points = mkRoute.polyline.points()
                var coords: [CLLocationCoordinate2D] = []
                for i in 0..<pointCount {
                    coords.append(points[i].coordinate)
                }
                directionsCoordinates = coords
                directionsETA = mkRoute.expectedTravelTime
                directionsDistance = mkRoute.distance
            }
        } catch {
            directionsCoordinates = []
            directionsETA = nil
            directionsDistance = nil
        }
    }

    private func clearDirections() {
        directionsCoordinates = []
        directionsETA = nil
        directionsDistance = nil
        lastDirectionsTarget = nil
        lastDirectionsLocation = nil
    }
}

// MARK: - Floating UI (State-Based)

extension CollectorRouteView {
    @ViewBuilder
    private func floatingUI(_ route: CollectionRoute) -> some View {
        switch route.status {
        case .pending:
            pendingLayout(route)
        case .accepted:
            acceptedLayout(route)
        case .inProgress:
            activeLayout(route)
        default:
            EmptyView()
        }
    }

    /// Minimal floating controls (top-right) for pending/accepted states
    private func floatingMapControls(_ route: CollectionRoute) -> some View {
        VStack(spacing: 8) {
            Button {
                withAnimation(.easeInOut(duration: 0.5)) {
                    cameraPosition = .automatic
                }
            } label: {
                Image(systemName: "location")
                    .font(.system(size: 14, weight: .semibold))
                    .foregroundStyle(.primary)
            }
            .frame(width: 40, height: 40)
            .background(.ultraThinMaterial, in: Circle())
            .shadow(color: .black.opacity(0.15), radius: 4, y: 2)

            if route.status == .pending {
                Button {
                    Task { await routeService.generateRoutes() }
                } label: {
                    Image(systemName: "arrow.triangle.2.circlepath")
                        .font(.system(size: 13, weight: .semibold))
                }
                .frame(width: 40, height: 40)
                .background(.ultraThinMaterial, in: Circle())
                .shadow(color: .black.opacity(0.15), radius: 4, y: 2)
                .disabled(routeService.isLoading)
            }
        }
    }
}

// MARK: - Pending State (Grab IMG_3477 Style)

extension CollectorRouteView {
    private func pendingLayout(_ route: CollectionRoute) -> some View {
        ZStack(alignment: .topTrailing) {
            VStack {
                Spacer()
                pendingSheet(route)
            }
            floatingMapControls(route)
                .padding(.top, 60)
                .padding(.trailing, 16)
        }
    }

    private func pendingSheet(_ route: CollectionRoute) -> some View {
        VStack(spacing: 0) {
            Capsule()
                .fill(.secondary.opacity(0.4))
                .frame(width: 36, height: 5)
                .padding(.top, 10)
                .padding(.bottom, 8)

            ScrollView {
                VStack(spacing: 14) {
                    HStack {
                        VStack(alignment: .leading, spacing: 2) {
                            Text("New Route")
                                .font(.headline)
                            if let depotName = route.depotName {
                                Text(depotName)
                                    .font(.caption)
                                    .foregroundStyle(.secondary)
                            }
                        }
                        Spacer()
                    }

                    statsRow(route)
                    compactStopList(route)

                    VStack(spacing: 10) {
                        Button {
                            HapticManager.impact(.medium)
                            Task { await routeService.acceptRoute(route.id) }
                        } label: {
                            Label("Accept Route", systemImage: "checkmark.circle.fill")
                                .font(.headline)
                                .frame(maxWidth: .infinity)
                                .frame(height: 52)
                        }
                        .buttonStyle(.borderedProminent)
                        .tint(.green)
                        .controlSize(.large)

                        Button(role: .destructive) {
                            Task { await routeService.rejectRoute(route.id) }
                        } label: {
                            Text("Reject")
                                .frame(maxWidth: .infinity)
                                .frame(height: 44)
                        }
                        .buttonStyle(.bordered)
                        .controlSize(.large)
                    }
                }
                .padding(.horizontal, 20)
                .padding(.bottom, 24)
            }
            .frame(maxHeight: 360)
        }
        .background(.regularMaterial, in: UnevenRoundedRectangle(topLeadingRadius: 20, topTrailingRadius: 20))
        .transition(.move(edge: .bottom))
    }
}

// MARK: - Accepted State (Google Maps IMG_3480 Style)

extension CollectorRouteView {
    private func acceptedLayout(_ route: CollectionRoute) -> some View {
        ZStack(alignment: .topTrailing) {
            VStack {
                Spacer()
                acceptedSheet(route)
            }
            floatingMapControls(route)
                .padding(.top, 60)
                .padding(.trailing, 16)
        }
    }

    private func acceptedSheet(_ route: CollectionRoute) -> some View {
        VStack(spacing: 0) {
            Capsule()
                .fill(.secondary.opacity(0.4))
                .frame(width: 36, height: 5)
                .padding(.top, 10)
                .padding(.bottom, 8)

            ScrollView {
                VStack(spacing: 14) {
                    HStack {
                        VStack(alignment: .leading, spacing: 2) {
                            HStack(spacing: 6) {
                                Image(systemName: "checkmark.circle.fill")
                                    .foregroundStyle(.green)
                                Text("Route Ready")
                                    .font(.headline)
                            }
                            if let depotName = route.depotName {
                                Text(depotName)
                                    .font(.caption)
                                    .foregroundStyle(.secondary)
                            }
                        }
                        Spacer()
                    }

                    statsRow(route)
                    compactStopList(route)

                    Button {
                        HapticManager.notification(.success)
                        Task { _ = await routeService.startRoute(route.id) }
                    } label: {
                        Label("Start Navigation", systemImage: "location.fill")
                            .font(.headline)
                            .frame(maxWidth: .infinity)
                            .frame(height: 52)
                    }
                    .buttonStyle(.borderedProminent)
                    .tint(.green)
                    .controlSize(.large)
                }
                .padding(.horizontal, 20)
                .padding(.bottom, 24)
            }
            .frame(maxHeight: 360)
        }
        .background(.regularMaterial, in: UnevenRoundedRectangle(topLeadingRadius: 20, topTrailingRadius: 20))
    }
}

// MARK: - Active Navigation (Google Maps IMG_3481 Style)

extension CollectorRouteView {
    /// Whether the re-centre button should show (user panned away during active nav)
    private var showRecentreButton: Bool {
        guard route?.status == .inProgress else { return false }
        return !isFollowingUser
    }

    private func activeLayout(_ route: CollectionRoute) -> some View {
        ZStack {
            VStack(spacing: 0) {
                // Dark teal instruction banner
                if let next = route.nextStop {
                    instructionBanner(next, route: route)
                        .padding(.horizontal, 16)
                        .padding(.top, 8)

                    // "Then → [next outlet]" chip
                    if let thenStop = stopAfterNext(in: route) {
                        thenChip(thenStop)
                            .padding(.top, 6)
                    }
                }

                Spacer()

                // Bottom bar — two-row normally, expands when arrived
                activeBottomBar(route)
                    .padding(.horizontal, 16)
                    .padding(.bottom, 8)
            }

            // Floating re-centre button (Google Maps pattern)
            if showRecentreButton {
                VStack {
                    Spacer()
                    HStack {
                        Spacer()
                        Button {
                            withAnimation(.easeInOut(duration: 0.5)) {
                                cameraPosition = .userLocation(followsHeading: true, fallback: .automatic)
                                isFollowingUser = true
                            }
                        } label: {
                            Image(systemName: "location.fill")
                                .font(.system(size: 16, weight: .semibold))
                                .foregroundStyle(.blue)
                                .frame(width: 44, height: 44)
                                .background(.ultraThinMaterial, in: Circle())
                                .shadow(color: .black.opacity(0.15), radius: 6, y: 3)
                        }
                        .padding(.trailing, 16)
                    }
                    .padding(.bottom, 130) // Above the bottom bar
                }
                .transition(.scale.combined(with: .opacity))
                .animation(.spring(response: 0.3), value: showRecentreButton)
            }
        }
    }

    // MARK: Instruction Banner

    private func instructionBanner(_ stop: RouteStop, route: CollectionRoute) -> some View {
        HStack(spacing: 12) {
            Image(systemName: "mappin.and.ellipse")
                .font(.system(size: 20, weight: .semibold))
                .foregroundStyle(.white)

            VStack(alignment: .leading, spacing: 3) {
                Text("Stop \(stop.stopOrder) · \((stop.outlet ?? "Unknown"))")
                    .font(.callout.bold())
                    .foregroundStyle(.white)
                    .lineLimit(1)

                Group {
                    if let dist = directionsDistance, lastDirectionsTarget == stop.stopOrder {
                        if let eta = directionsETA {
                            Text("\(formatDistance(dist)) · ~\(formatETA(eta))")
                        } else {
                            Text(formatDistance(dist))
                        }
                    } else if let userLoc = locationManager.userLocation {
                        Text(stop.distanceFormatted(from: userLoc))
                    }
                }
                .font(.subheadline.bold())
                .foregroundStyle(.cyan)
            }

            Spacer()

            // Compass / re-centre
            Button {
                withAnimation(.easeInOut(duration: 0.5)) {
                    cameraPosition = .userLocation(followsHeading: true, fallback: .automatic)
                    isFollowingUser = true
                }
            } label: {
                Image(systemName: "location.north.fill")
                    .font(.system(size: 14, weight: .bold))
                    .foregroundStyle(.white)
                    .frame(width: 40, height: 40)
                    .background(.white.opacity(0.2), in: Circle())
            }
        }
        .padding(.horizontal, 16)
        .padding(.vertical, 16)
        .background(
            RoundedRectangle(cornerRadius: 18)
                .fill(Color(red: 0.0, green: 0.30, blue: 0.35))
                .shadow(color: .black.opacity(0.3), radius: 8, y: 4)
        )
    }

    // MARK: Then Chip

    private func thenChip(_ stop: RouteStop) -> some View {
        HStack(spacing: 4) {
            Text("Then")
                .foregroundStyle(.white.opacity(0.7))
            Image(systemName: "arrow.right")
                .font(.system(size: 9, weight: .bold))
                .foregroundStyle(.white.opacity(0.5))
            Text((stop.outlet ?? "Unknown"))
                .foregroundStyle(.white)
                .lineLimit(1)
        }
        .font(.caption.bold())
        .padding(.horizontal, 12)
        .padding(.vertical, 6)
        .background(Capsule().fill(.black.opacity(0.5)))
    }

    private func stopAfterNext(in route: CollectionRoute) -> RouteStop? {
        guard let next = route.nextStop else { return nil }
        return route.stops.first { $0.order > next.order && $0.isPending }
    }

    // MARK: Bottom Bar

    @ViewBuilder
    private func activeBottomBar(_ route: CollectionRoute) -> some View {
        let next = route.nextStop
        let userLoc = locationManager.userLocation
        let distance = next.flatMap { stop in userLoc.map { stop.distanceMeters(from: $0) } }
        let isArrived = distance.map { $0 <= route.proximityThreshold } ?? false

        if isArrived, let next {
            arrivedPanel(next, route: route)
        } else {
            thinActionBar(route)
        }
    }

    /// Two-row bottom bar — Row 1: progress info, Row 2: action buttons (48pt min)
    private func thinActionBar(_ route: CollectionRoute) -> some View {
        VStack(spacing: 8) {
            // Row 1: Progress dots + count + ETA
            HStack(spacing: 6) {
                ForEach(route.stops) { stop in
                    Circle()
                        .fill(stop.isCompleted ? .green : stop.isSkipped ? .gray : stop.stopOrder == route.nextStop?.order ? .orange : .white.opacity(0.3))
                        .frame(width: 10, height: 10)
                }

                Spacer()

                Text("\(route.stopsCompleted) of \(route.stopsTotal) stops")
                    .font(.callout.bold())
                    .foregroundStyle(.white)

                if let eta = directionsETA, let next = route.nextStop, lastDirectionsTarget == next.order {
                    Text("· ~\(formatETA(eta))")
                        .font(.callout.bold())
                        .foregroundStyle(.cyan)
                }
            }

            // Row 2: Action buttons — all 48pt tall minimum
            HStack(spacing: 10) {
                // Exit
                Button {
                    showExitAlert = true
                } label: {
                    Text("Exit")
                        .font(.subheadline.bold())
                        .frame(height: 48)
                        .padding(.horizontal, 14)
                }
                .buttonStyle(.borderedProminent)
                .tint(.red)
                .buttonBorderShape(.capsule)
                .alert("Leave Route?", isPresented: $showExitAlert) {
                    Button("Stay", role: .cancel) {}
                    Button("Leave") { dismiss() }
                } message: {
                    Text("Your route will remain active. You can return from the Home tab.")
                }

                if let next = route.nextStop {
                    let userLoc = locationManager.userLocation
                    let distance = userLoc.map { next.distanceMeters(from: $0) }
                    let withinRange = distance.map { $0 <= route.proximityThreshold } ?? false

                    // Navigate (Apple Maps handoff)
                    Button {
                        openInMaps(next)
                    } label: {
                        Label("Navigate", systemImage: "arrow.triangle.turn.up.right.diamond.fill")
                            .font(.subheadline.bold())
                            .frame(height: 48)
                    }
                    .buttonStyle(.borderedProminent)
                    .tint(.blue)
                    .buttonBorderShape(.capsule)

                    // Complete (or distance when far)
                    Button {
                        guard let loc = locationManager.userLocation else { return }
                        HapticManager.notification(.success)
                        Task {
                            _ = await routeService.completeStop(
                                routeId: route.id, order: next.order,
                                latitude: loc.latitude, longitude: loc.longitude
                            )
                            checkAutoComplete(route)
                        }
                    } label: {
                        Group {
                            if let d = distance {
                                Text(withinRange ? "Complete" : formatDistance(d))
                            } else {
                                Text("No GPS")
                            }
                        }
                        .font(.subheadline.bold())
                        .frame(height: 48)
                        .padding(.horizontal, 8)
                    }
                    .buttonStyle(.borderedProminent)
                    .tint(withinRange ? .green : .gray)
                    .buttonBorderShape(.capsule)
                    .disabled(!withinRange)

                    // Skip
                    Button {
                        stopToSkip = next
                        showSkipAlert = true
                    } label: {
                        Image(systemName: "forward.fill")
                            .font(.system(size: 13, weight: .bold))
                            .frame(width: 48, height: 48)
                    }
                    .buttonStyle(.bordered)
                    .tint(.gray)
                    .buttonBorderShape(.capsule)
                }
            }
        }
        .padding(.horizontal, 14)
        .padding(.vertical, 12)
        .background(
            RoundedRectangle(cornerRadius: 20)
                .fill(Color(red: 0.07, green: 0.07, blue: 0.08))
                .shadow(color: .black.opacity(0.3), radius: 8, y: 4)
        )
    }

    /// Expanded arrival card — prominent "You've arrived" with large Complete button
    private func arrivedPanel(_ stop: RouteStop, route: CollectionRoute) -> some View {
        VStack(spacing: 14) {
            // Header: "You've arrived" + Exit X
            HStack {
                HStack(spacing: 8) {
                    Circle()
                        .fill(.green)
                        .frame(width: 10, height: 10)
                        .overlay(
                            Circle()
                                .stroke(.green.opacity(0.4), lineWidth: 2)
                                .scaleEffect(arrivedPulse ? 2.0 : 1.0)
                                .opacity(arrivedPulse ? 0.0 : 0.6)
                        )
                    Text("You've arrived")
                        .font(.subheadline.bold())
                        .foregroundStyle(.green)
                }
                Spacer()
                Button {
                    showExitAlert = true
                } label: {
                    Image(systemName: "xmark")
                        .font(.system(size: 11, weight: .bold))
                        .foregroundStyle(.white.opacity(0.5))
                        .frame(width: 28, height: 28)
                        .background(.white.opacity(0.1), in: Circle())
                }
            }

            // Outlet info
            VStack(alignment: .leading, spacing: 4) {
                Text((stop.outlet ?? "Unknown"))
                    .font(.headline.bold())
                    .foregroundStyle(.white)
                Text(stop.address)
                    .font(.caption)
                    .foregroundStyle(.white.opacity(0.6))
                    .lineLimit(2)
            }
            .frame(maxWidth: .infinity, alignment: .leading)

            // Fill bar — larger
            HStack(spacing: 10) {
                Text("Fill")
                    .font(.caption.bold())
                    .foregroundStyle(.white.opacity(0.6))
                GeometryReader { geo in
                    ZStack(alignment: .leading) {
                        RoundedRectangle(cornerRadius: 5)
                            .fill(.white.opacity(0.15))
                        RoundedRectangle(cornerRadius: 5)
                            .fill(fillColor(stop.fillLevel))
                            .frame(width: geo.size.width * Double(stop.fillLevel) / 100)
                    }
                }
                .frame(height: 10)
                Text("\(stop.fillLevel)%")
                    .font(.callout.bold().monospacedDigit())
                    .foregroundStyle(fillColor(stop.fillLevel))
            }

            // Primary: Complete Pickup — FULL WIDTH, 54pt
            Button {
                guard let loc = locationManager.userLocation else { return }
                HapticManager.notification(.success)
                Task {
                    _ = await routeService.completeStop(
                        routeId: route.id, order: stop.stopOrder,
                        latitude: loc.latitude, longitude: loc.longitude
                    )
                    checkAutoComplete(route)
                }
            } label: {
                Label("Complete Pickup", systemImage: "checkmark.circle.fill")
                    .font(.headline)
                    .frame(maxWidth: .infinity)
                    .frame(height: 54)
            }
            .buttonStyle(.borderedProminent)
            .tint(.green)
            .buttonBorderShape(.capsule)

            // Secondary row: Skip + Navigate
            HStack(spacing: 10) {
                Button {
                    stopToSkip = stop
                    showSkipAlert = true
                } label: {
                    Text("Skip")
                        .font(.subheadline.bold())
                        .frame(height: 40)
                        .frame(maxWidth: .infinity)
                }
                .buttonStyle(.bordered)
                .tint(.gray)
                .buttonBorderShape(.capsule)

                Button {
                    openInMaps(stop)
                } label: {
                    Label("Navigate", systemImage: "arrow.triangle.turn.up.right.diamond.fill")
                        .font(.subheadline.bold())
                        .frame(height: 40)
                        .frame(maxWidth: .infinity)
                }
                .buttonStyle(.borderedProminent)
                .tint(.blue)
                .buttonBorderShape(.capsule)
            }
        }
        .padding(18)
        .background(
            RoundedRectangle(cornerRadius: 20)
                .fill(Color(red: 0.07, green: 0.07, blue: 0.08))
                .shadow(color: .black.opacity(0.3), radius: 8, y: 4)
        )
        .onAppear {
            HapticManager.notification(.success)
            withAnimation(.easeInOut(duration: 1.2).repeatForever(autoreverses: true)) {
                arrivedPulse = true
            }
        }
        .onDisappear { arrivedPulse = false }
    }
}

// MARK: - Shared Components

extension CollectorRouteView {
    private func statsRow(_ route: CollectionRoute) -> some View {
        HStack(spacing: 0) {
            statCell(icon: "mappin.circle.fill", value: "\(route.stopsTotal)", label: "Stops", color: .red)
            Divider().frame(height: 30)
            statCell(icon: "road.lanes", value: route.totalDistanceKm.map { String(format: "%.1f km", $0) } ?? "--", label: "Distance", color: .blue)
            Divider().frame(height: 30)
            statCell(icon: "clock.fill", value: route.totalDurationMin.map { "~\($0) min" } ?? "--", label: "Duration", color: .orange)
        }
        .padding(.vertical, 10)
        .background(.quaternary.opacity(0.3), in: RoundedRectangle(cornerRadius: 12))
    }

    private func statCell(icon: String, value: String, label: String, color: Color) -> some View {
        VStack(spacing: 3) {
            Image(systemName: icon)
                .font(.system(size: 12))
                .foregroundStyle(color)
            Text(value)
                .font(.subheadline.bold())
            Text(label)
                .font(.caption2)
                .foregroundStyle(.secondary)
        }
        .frame(maxWidth: .infinity)
    }

    private func compactStopList(_ route: CollectionRoute) -> some View {
        VStack(spacing: 0) {
            ForEach(route.stops) { stop in
                HStack(spacing: 10) {
                    ZStack {
                        Circle()
                            .fill(stopColor(stop, route: route))
                            .frame(width: 28, height: 28)
                        Text("\(stop.stopOrder)")
                            .font(.system(size: 12, weight: .bold))
                            .foregroundStyle(.white)
                    }

                    VStack(alignment: .leading, spacing: 1) {
                        Text((stop.outlet ?? "Unknown"))
                            .font(.subheadline)
                            .lineLimit(1)
                        Text(stop.address)
                            .font(.caption2)
                            .foregroundStyle(.secondary)
                            .lineLimit(1)
                    }

                    Spacer()

                    Text("\(stop.fillLevel)%")
                        .font(.caption.bold().monospacedDigit())
                        .foregroundStyle(fillColor(stop.fillLevel))
                }
                .padding(.vertical, 8)
                .padding(.horizontal, 12)
                .contentShape(Rectangle())
                .onTapGesture {
                    withAnimation(.easeInOut(duration: 0.5)) {
                        cameraPosition = .region(
                            MKCoordinateRegion(
                                center: (stop.coordinate ?? CLLocationCoordinate2D()),
                                span: MKCoordinateSpan(latitudeDelta: 0.006, longitudeDelta: 0.006)
                            )
                        )
                    }
                }

                if stop.stopOrder != route.stops.last?.order {
                    Divider().padding(.leading, 50)
                }
            }
        }
        .background(.quaternary.opacity(0.15), in: RoundedRectangle(cornerRadius: 12))
    }
}

// MARK: - Empty State

extension CollectorRouteView {
    private var emptyOverlay: some View {
        VStack {
            Spacer()
            VStack(spacing: 12) {
                Image(systemName: "map.fill")
                    .font(.system(size: 40))
                    .foregroundStyle(.secondary)
                Text("No Active Route")
                    .font(.title3.bold())
                Text("Routes are generated when bins\nin your zone need collection.")
                    .font(.subheadline)
                    .foregroundStyle(.secondary)
                    .multilineTextAlignment(.center)

                Button {
                    Task { await routeService.fetchRoutes() }
                } label: {
                    Label("Refresh", systemImage: "arrow.clockwise")
                        .font(.subheadline.bold())
                }
                .buttonStyle(.borderedProminent)
                .tint(.green)
                .padding(.top, 4)
            }
            .padding(24)
            .background(.ultraThinMaterial, in: RoundedRectangle(cornerRadius: 20))
            .padding(.horizontal, 32)
            Spacer()
        }
    }
}

// MARK: - Map Pins

struct DepotPin: View {
    var body: some View {
        VStack(spacing: 0) {
            ZStack {
                Circle()
                    .fill(.indigo)
                    .frame(width: 38, height: 38)
                    .shadow(color: .indigo.opacity(0.4), radius: 5, y: 2)
                Image(systemName: "building.2.fill")
                    .font(.system(size: 15, weight: .bold))
                    .foregroundStyle(.white)
            }
            Triangle()
                .fill(.indigo)
                .frame(width: 12, height: 7)
                .offset(y: -1)
        }
    }
}

struct StopPin: View {
    let stop: RouteStop
    let isNext: Bool
    @State private var isPulsing = false

    var body: some View {
        VStack(spacing: 0) {
            ZStack {
                if isNext {
                    Circle()
                        .stroke(Color.green.opacity(isPulsing ? 0.0 : 0.4), lineWidth: 2)
                        .frame(width: 52, height: 52)
                        .scaleEffect(isPulsing ? 1.4 : 1.0)
                        .animation(.easeInOut(duration: 1.5).repeatForever(autoreverses: true), value: isPulsing)
                        .onAppear { isPulsing = true }
                }

                Circle()
                    .fill(color)
                    .frame(width: isNext ? 44 : 38, height: isNext ? 44 : 38)
                    .shadow(color: color.opacity(0.5), radius: isNext ? 8 : 5, y: 3)

                if stop.isCompleted {
                    Image(systemName: "checkmark")
                        .font(.system(size: isNext ? 18 : 16, weight: .bold))
                        .foregroundStyle(.white)
                } else if stop.isSkipped {
                    Image(systemName: "forward.fill")
                        .font(.system(size: 14, weight: .bold))
                        .foregroundStyle(.white)
                } else {
                    Text("\(stop.stopOrder)")
                        .font(.system(size: isNext ? 19 : 16, weight: .heavy))
                        .foregroundStyle(.white)
                }
            }
            Triangle()
                .fill(color)
                .frame(width: 12, height: 7)
                .offset(y: -1)
        }
    }

    private var color: Color {
        if stop.isCompleted { return .green }
        if stop.isSkipped { return .gray }
        if isNext { return .green }
        return .blue
    }
}

struct Triangle: Shape {
    func path(in rect: CGRect) -> Path {
        var path = Path()
        path.move(to: CGPoint(x: rect.midX, y: rect.maxY))
        path.addLine(to: CGPoint(x: rect.minX, y: rect.minY))
        path.addLine(to: CGPoint(x: rect.maxX, y: rect.minY))
        path.closeSubpath()
        return path
    }
}

// MARK: - Route Completion Sheet

struct RouteCompletionSheet: View {
    let route: CollectionRoute
    let onDismiss: () -> Void

    @State private var showCheckmark = false
    @State private var showContent = false

    var body: some View {
        VStack(spacing: 24) {
            Spacer()

            // Animated checkmark
            Image(systemName: "checkmark.circle.fill")
                .font(.system(size: 72))
                .foregroundStyle(.green)
                .scaleEffect(showCheckmark ? 1.0 : 0.3)
                .rotationEffect(.degrees(showCheckmark ? 0 : -30))
                .opacity(showCheckmark ? 1.0 : 0.0)

            VStack(spacing: 6) {
                Text("Route Complete!")
                    .font(.title.bold())
                Text("Great work out there")
                    .font(.subheadline)
                    .foregroundStyle(.secondary)
            }
            .opacity(showContent ? 1 : 0)
            .offset(y: showContent ? 0 : 10)

            completionStats
                .opacity(showContent ? 1 : 0)
                .offset(y: showContent ? 0 : 10)

            Spacer()

            Button {
                HapticManager.impact(.medium)
                onDismiss()
            } label: {
                Text("Done")
                    .font(.headline)
                    .frame(maxWidth: .infinity)
                    .frame(height: 52)
            }
            .buttonStyle(.borderedProminent)
            .tint(.green)
            .controlSize(.large)
            .opacity(showContent ? 1 : 0)
        }
        .padding(24)
        .onAppear {
            HapticManager.notification(.success)
            withAnimation(.spring(response: 0.5, dampingFraction: 0.6)) {
                showCheckmark = true
            }
            withAnimation(.easeOut(duration: 0.4).delay(0.3)) {
                showContent = true
            }
        }
    }

    private var completionStats: some View {
        VStack(spacing: 14) {
            completionRow(icon: "mappin.circle.fill", label: "Stops Completed", value: "\(route.stopsCompleted) of \(route.stopsTotal)")
            completionRow(icon: "road.lanes", label: "Distance", value: route.totalDistanceKm.map { String(format: "%.1f km", $0) } ?? "--")

            // Elapsed time
            if let started = route.startedAt, let completed = route.completedAt {
                let elapsed = Int(completed.timeIntervalSince(started) / 60)
                completionRow(icon: "timer", label: "Time Elapsed", value: "\(elapsed) min")
            } else {
                completionRow(icon: "clock.fill", label: "Est. Duration", value: route.totalDurationMin.map { "\($0) min" } ?? "--")
            }

            if let depotName = route.depotName {
                completionRow(icon: "map.fill", label: "Depot", value: depotName)
            }
        }
        .padding(20)
        .background(.quaternary.opacity(0.3), in: RoundedRectangle(cornerRadius: 16))
    }

    private func completionRow(icon: String, label: String, value: String) -> some View {
        HStack {
            Image(systemName: icon)
                .foregroundStyle(.secondary)
                .frame(width: 24)
            Text(label)
                .foregroundStyle(.secondary)
            Spacer()
            Text(value)
                .fontWeight(.semibold)
        }
    }
}

// MARK: - Helpers

extension CollectorRouteView {
    private func statusColor(_ status: RouteStatus) -> Color {
        switch status {
        case .pending: .orange
        case .accepted: .blue
        case .inProgress: .green
        case .completed: .gray
        case .cancelled: .red
        }
    }

    private func fillColor(_ level: Int) -> Color {
        switch level {
        case 0..<40: .green
        case 40..<70: .orange
        default: .red
        }
    }

    private func stopColor(_ stop: RouteStop, route: CollectionRoute) -> Color {
        if stop.isCompleted { return .green }
        if stop.isSkipped { return .gray }
        if stop.stopOrder == route.nextStop?.order { return .orange }
        return .blue
    }

    private func openInMaps(_ stop: RouteStop) {
        let placemark = MKPlacemark(coordinate: (stop.coordinate ?? CLLocationCoordinate2D()))
        let item = MKMapItem(placemark: placemark)
        item.name = (stop.outlet ?? "Unknown")
        item.openInMaps(launchOptions: [
            MKLaunchOptionsDirectionsModeKey: MKLaunchOptionsDirectionsModeDriving,
        ])
    }

    private func performSkip() {
        guard let stop = stopToSkip, let route else { return }
        Task {
            _ = await routeService.skipStop(routeId: route.id, order: stop.stopOrder, reason: skipReason)
            skipReason = ""
            stopToSkip = nil
            checkAutoComplete(route)
        }
    }

    private func checkAutoComplete(_ route: CollectionRoute) {
        Task {
            await routeService.fetchRoutes()
            if let updated = routeService.routes.first(where: { $0.id == route.id }),
               updated.status == .inProgress,
               updated.stops.allSatisfy({ $0.isCompleted || $0.isSkipped }) {
                _ = await routeService.completeRoute(updated.id)
            }
        }
    }

    private func formatDistance(_ meters: CLLocationDistance) -> String {
        if meters < 1000 {
            return "\(Int(meters))m"
        }
        return String(format: "%.1f km", meters / 1000)
    }

    private func formatETA(_ seconds: TimeInterval) -> String {
        let minutes = Int(seconds / 60)
        if minutes < 1 { return "<1 min" }
        if minutes == 1 { return "1 min" }
        return "\(minutes) min"
    }
}

// MARK: - RouteStop Hashable (for map selection)

extension RouteStop: Hashable {
    static func == (lhs: RouteStop, rhs: RouteStop) -> Bool {
        lhs.order == rhs.order
    }

    func hash(into hasher: inout Hasher) {
        hasher.combine(order)
    }
}

// MARK: - RouteStop Haversine Helper (static)

extension RouteStop {
    static func haversineDistance(from a: CLLocationCoordinate2D, to b: CLLocationCoordinate2D) -> Double {
        let earthRadius: Double = 6_371_000
        let dLat = (b.latitude - a.latitude) * .pi / 180
        let dLng = (b.longitude - a.longitude) * .pi / 180
        let x = sin(dLat / 2) * sin(dLat / 2)
            + cos(a.latitude * .pi / 180) * cos(b.latitude * .pi / 180)
            * sin(dLng / 2) * sin(dLng / 2)
        let c = 2 * atan2(sqrt(x), sqrt(1 - x))
        return earthRadius * c
    }
}

// MARK: - Previews

#Preview("Active Route") {
    NavigationStack {
        CollectorRouteView()
    }
    .environment(AuthManager.mockAuthenticated())
    .environment(RoleManager.mockMultiRole())
    .environment(RouteService.mockWithActiveRoute())
}

#Preview("Pending Route") {
    NavigationStack {
        CollectorRouteView()
    }
    .environment(AuthManager.mockAuthenticated())
    .environment(RoleManager.mockMultiRole())
    .environment(RouteService.mockWithPendingRoute())
}
