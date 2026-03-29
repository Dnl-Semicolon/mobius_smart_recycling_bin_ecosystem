import SwiftUI

/// The main tab view that changes its tabs based on the user's active role.
/// This is the core navigation hub — like TnG switching between personal and business.
///
/// Uses a SINGLE TabView with an `id` keyed to the active role. This forces SwiftUI
/// to rebuild the TabView cleanly on role switch, avoiding the transparent tab bar bug
/// that occurs when swapping between separate TabView structs.
struct MainTabView: View {
    @Environment(AuthManager.self) private var auth
    @Environment(RoleManager.self) private var roleManager
    @Environment(NotificationManager.self) private var notificationManager
    @Environment(\.scenePhase) private var scenePhase

    var body: some View {
        TabView {
            switch roleManager.activeRole {
            case .publicUser, .admin:
                customerTabs
            case .collector:
                collectorTabs
            case .storeOwner:
                storeOwnerTabs
            }

            Tab("Profile", systemImage: "person.fill") {
                NavigationStack {
                    ProfileView()
                }
            }
        }
        .id(roleManager.activeRole)
        .tint(roleManager.activeRole.iconColor)
        .toolbarBackground(.visible, for: .tabBar)
        .onChange(of: auth.currentUser) { _, user in
            if let user {
                roleManager.configure(for: user)
            }
        }
        .onAppear {
            if let user = auth.currentUser {
                roleManager.configure(for: user)
            }
        }
        .task {
            await notificationManager.fetchUnreadCount()
        }
        .onChange(of: scenePhase) { _, phase in
            if phase == .active {
                Task { await notificationManager.fetchUnreadCount() }
            }
        }
    }

    // MARK: - Customer Tabs

    @TabContentBuilder<Never> private var customerTabs: some TabContent<Never> {
        Tab("Home", systemImage: "house.fill") {
            NavigationStack {
                CustomerHomeView()
            }
        }

        Tab("Scan", systemImage: "qrcode.viewfinder") {
            NavigationStack {
                ScanView()
            }
        }

        Tab("Rewards", systemImage: "gift.fill") {
            NavigationStack {
                RewardsView()
            }
        }
    }

    // MARK: - Collector Tabs (3 tabs — Grab pattern: Home / Stats / Profile)

    @TabContentBuilder<Never> private var collectorTabs: some TabContent<Never> {
        Tab("Home", systemImage: "house.fill") {
            NavigationStack {
                CollectorHomeView()
            }
        }

        Tab("Stats", systemImage: "chart.bar.fill") {
            NavigationStack {
                CollectorStatsView()
            }
        }
    }

    // MARK: - Store Owner Tabs

    @TabContentBuilder<Never> private var storeOwnerTabs: some TabContent<Never> {
        Tab("Dashboard", systemImage: "storefront.fill") {
            NavigationStack {
                StoreOwnerDashboardView()
            }
        }

        Tab("Bins", systemImage: "trash.fill") {
            NavigationStack {
                StoreOwnerBinsView()
            }
        }

        Tab("Analytics", systemImage: "chart.line.uptrend.xyaxis") {
            NavigationStack {
                StoreOwnerAnalyticsView()
            }
        }
    }
}

#Preview("Multi-role") {
    MainTabView()
        .environment(AuthManager.mockAuthenticated())
        .environment(RoleManager.mockMultiRole())
        .environment(NotificationManager.mockWithNotifications())
}
