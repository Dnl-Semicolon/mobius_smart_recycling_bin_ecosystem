import SwiftUI

struct RootView: View {
    @Environment(AuthManager.self) private var auth

    var body: some View {
        Group {
            if auth.isLoading {
                LaunchScreen()
            } else if auth.isAuthenticated {
                MainTabView()
            } else {
                AuthNavigationView()
            }
        }
        .animation(.easeInOut(duration: 0.3), value: auth.isAuthenticated)
        .animation(.easeInOut(duration: 0.3), value: auth.isLoading)
    }
}

struct LaunchScreen: View {
    var body: some View {
        VStack(spacing: 16) {
            Image("MobiusIcon")
                .resizable()
                .scaledToFit()
                .frame(height: 80)
            Image("MobiusWordmark")
                .resizable()
                .scaledToFit()
                .frame(height: 28)
            Text("Smart Recycling")
                .font(.subheadline)
                .foregroundStyle(.secondary)
        }
    }
}

#Preview {
    RootView()
        .environment(AuthManager())
        .environment(RoleManager())
}
