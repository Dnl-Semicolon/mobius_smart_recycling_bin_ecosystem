import SwiftUI
import UIKit

@main
struct MobiusApp: App {
    @State private var authManager = AuthManager()
    @State private var roleManager = RoleManager()
    @State private var notificationManager = NotificationManager()
    @State private var customerService = CustomerService()
    @State private var collectorService = CollectorService()
    @State private var routeService = RouteService()
    @State private var rewardService = RewardService()

    init() {
        // Force opaque tab bar background — fixes liquid glass transparency bug
        // when TabView is recreated (e.g. role switching).
        let appearance = UITabBarAppearance()
        appearance.configureWithDefaultBackground()
        UITabBar.appearance().standardAppearance = appearance
        UITabBar.appearance().scrollEdgeAppearance = appearance
    }

    var body: some Scene {
        WindowGroup {
            RootView()
                .environment(authManager)
                .environment(roleManager)
                .environment(notificationManager)
                .environment(customerService)
                .environment(collectorService)
                .environment(routeService)
                .environment(rewardService)
        }
    }
}
