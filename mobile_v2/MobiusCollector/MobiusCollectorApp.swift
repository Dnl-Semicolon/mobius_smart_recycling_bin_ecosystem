import SwiftUI

@main
struct MobiusCollectorApp: App {
    @State private var authVM = AuthViewModel()

    var body: some Scene {
        WindowGroup {
            Group {
                if authVM.isAuthenticated {
                    CollectorHomeView()
                } else {
                    LoginView()
                }
            }
            .animation(.default, value: authVM.isAuthenticated)
            .environment(authVM)
        }
    }
}

extension Color {
    static let mobiusTeal = Color(red: 13 / 255, green: 148 / 255, blue: 136 / 255)
}
