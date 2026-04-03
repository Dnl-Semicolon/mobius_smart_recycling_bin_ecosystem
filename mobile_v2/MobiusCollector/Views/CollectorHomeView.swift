import SwiftUI

struct CollectorHomeView: View {
    @Environment(AuthViewModel.self) private var authVM
    @State private var routeVM = RouteViewModel()

    private var pendingCount: Int {
        routeVM.routes.filter { $0.status == "pending" || $0.status == "accepted" }.count
    }

    private var completedCount: Int {
        routeVM.routes.filter { $0.status == "completed" }.count
    }

    var body: some View {
        NavigationStack {
            List {
                // Stats
                Section {
                    HStack(spacing: 16) {
                        StatCard(title: "Pending", value: "\(pendingCount)", color: .orange)
                        StatCard(title: "Completed", value: "\(completedCount)", color: .green)
                    }
                    .listRowInsets(EdgeInsets())
                    .listRowBackground(Color.clear)
                }

                // Generate
                Section {
                    Button {
                        Task { await routeVM.generateRoute() }
                    } label: {
                        HStack {
                            if routeVM.isGenerating {
                                ProgressView()
                                    .controlSize(.small)
                            } else {
                                Image(systemName: "plus.circle.fill")
                            }
                            Text(routeVM.isGenerating ? "Generating..." : "Generate New Route")
                        }
                        .font(.headline)
                        .frame(maxWidth: .infinity)
                        .padding(.vertical, 8)
                    }
                    .tint(Color.mobiusTeal)
                    .disabled(routeVM.isGenerating)
                }

                // Route list
                Section("Routes") {
                    if routeVM.routes.isEmpty && !routeVM.isLoading {
                        ContentUnavailableView(
                            "No Routes",
                            systemImage: "map",
                            description: Text("Generate a route to get started")
                        )
                        .listRowBackground(Color.clear)
                    } else {
                        ForEach(routeVM.routes) { route in
                            NavigationLink(value: route.id) {
                                RouteRow(route: route)
                            }
                        }
                    }
                }
            }
            .navigationTitle("Collection Routes")
            .navigationDestination(for: Int.self) { routeId in
                RouteDetailView(routeId: routeId, routeVM: routeVM)
            }
            .toolbar {
                ToolbarItem(placement: .topBarLeading) {
                    if let user = authVM.user {
                        Text("Hi, \(user.name.components(separatedBy: " ").first ?? user.name)")
                            .font(.subheadline)
                            .foregroundStyle(.secondary)
                    }
                }
                ToolbarItem(placement: .topBarTrailing) {
                    Menu {
                        if let user = authVM.user {
                            Label(user.name, systemImage: "person")
                            Label(user.email, systemImage: "envelope")
                        }
                        Divider()
                        Button("Sign Out", role: .destructive) {
                            authVM.logout()
                        }
                    } label: {
                        Image(systemName: "person.circle")
                    }
                }
            }
            .refreshable {
                await routeVM.fetchRoutes()
            }
            .task {
                await routeVM.fetchRoutes()
            }
            .overlay {
                if routeVM.isLoading && routeVM.routes.isEmpty {
                    ProgressView()
                }
            }
            .alert("Error", isPresented: Binding(
                get: { routeVM.errorMessage != nil },
                set: { if !$0 { routeVM.errorMessage = nil } }
            )) {
                Button("OK") { routeVM.errorMessage = nil }
            } message: {
                Text(routeVM.errorMessage ?? "")
            }
        }
    }
}

// MARK: - Subviews

struct StatCard: View {
    let title: String
    let value: String
    let color: Color

    var body: some View {
        VStack(spacing: 4) {
            Text(value)
                .font(.title.bold())
                .foregroundStyle(color)
            Text(title)
                .font(.caption)
                .foregroundStyle(.secondary)
        }
        .frame(maxWidth: .infinity)
        .padding()
        .background(color.opacity(0.1), in: RoundedRectangle(cornerRadius: 12))
    }
}

struct RouteRow: View {
    let route: CollectionRoute

    var body: some View {
        VStack(alignment: .leading, spacing: 8) {
            HStack {
                StatusBadge(status: route.status)
                Spacer()
                if let stops = route.stopsCount {
                    Label("\(stops) stops", systemImage: "mappin.and.ellipse")
                        .font(.caption)
                        .foregroundStyle(.secondary)
                }
            }

            if let depot = route.depotName {
                Text(depot)
                    .font(.headline)
            }

            HStack(spacing: 16) {
                if let dist = route.totalDistanceKm {
                    Label(String(format: "%.1f km", dist), systemImage: "road.lanes")
                }
                if let dur = route.totalDurationMin {
                    Label("\(dur) min", systemImage: "clock")
                }
            }
            .font(.subheadline)
            .foregroundStyle(.secondary)
        }
        .padding(.vertical, 4)
    }
}

struct StatusBadge: View {
    let status: String

    private var color: Color {
        switch status {
        case "pending": .orange
        case "accepted": .blue
        case "in_progress": .mobiusTeal
        case "completed": .green
        case "rejected": .red
        default: .gray
        }
    }

    private var label: String {
        switch status {
        case "in_progress": "In Progress"
        default: status.capitalized
        }
    }

    var body: some View {
        Text(label)
            .font(.caption.bold())
            .padding(.horizontal, 8)
            .padding(.vertical, 4)
            .background(color.opacity(0.15), in: Capsule())
            .foregroundStyle(color)
    }
}
