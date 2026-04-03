import Foundation

@MainActor
@Observable
final class AuthViewModel {
    var email = ""
    var password = ""
    var user: User?
    var isLoading = false
    var errorMessage: String?

    var isAuthenticated: Bool { user != nil }

    init() {
        if let data = UserDefaults.standard.data(forKey: "user_data") {
            user = try? JSONDecoder().decode(User.self, from: data)
        }
    }

    func login() async {
        isLoading = true
        errorMessage = nil

        do {
            let response: LoginResponse = try await APIService.shared.request(
                method: "POST",
                path: "/auth/login",
                body: ["email": email, "password": password, "device_name": "MobiusCollector-iOS"]
            )
            APIService.shared.token = response.token
            user = response.user
            if let data = try? JSONEncoder().encode(response.user) {
                UserDefaults.standard.set(data, forKey: "user_data")
            }
        } catch {
            errorMessage = error.localizedDescription
        }

        isLoading = false
    }

    func logout() {
        APIService.shared.token = nil
        user = nil
        email = ""
        password = ""
        UserDefaults.standard.removeObject(forKey: "user_data")
    }
}
