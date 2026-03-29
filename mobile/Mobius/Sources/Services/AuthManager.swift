import Foundation
import UIKit

/// Manages authentication state for the entire app.
/// Uses @Observable (Swift 5.9+) — the modern replacement for ObservableObject.
@MainActor
@Observable
final class AuthManager {
    private(set) var currentUser: User?
    private(set) var isLoading = true
    private(set) var error: String?

    private let api = APIClient()

    var isAuthenticated: Bool {
        currentUser != nil
    }

    // MARK: - Lifecycle

    init() {
        // Check for existing token on app launch
        if api.hasToken {
            Task { @MainActor in
                await self.fetchCurrentUser()
            }
        } else {
            isLoading = false
        }
    }

    // MARK: - Auth Actions

    func login(email: String, password: String) async {
        error = nil
        isLoading = true
        defer { isLoading = false }

        do {
            let deviceName = UIDevice.current.name
            let response: LoginResponse = try await api.post("/auth/login", body: LoginRequest(email: email, password: password, deviceName: deviceName))
            api.setToken(response.token)
            currentUser = response.user
        } catch let apiError as APIError {
            error = apiError.errorDescription
        } catch {
            self.error = "Connection failed. Is the server running?"
        }
    }

    /// Bypass API and load mock user — for demos and offline development
    func loginDemo() {
        isLoading = false
        error = nil
        currentUser = .mock
    }

    func register(name: String, email: String, password: String, passwordConfirmation: String) async {
        error = nil
        isLoading = true
        defer { isLoading = false }

        do {
            let deviceName = UIDevice.current.name
            let response: LoginResponse = try await api.post("/auth/register", body: RegisterRequest(
                name: name,
                email: email,
                password: password,
                passwordConfirmation: passwordConfirmation,
                deviceName: deviceName
            ))
            api.setToken(response.token)
            currentUser = response.user
        } catch let apiError as APIError {
            error = apiError.errorDescription
        } catch {
            self.error = "Registration failed. Please try again."
        }
    }

    func logout() async {
        do {
            try await api.delete("/auth/logout")
        } catch {
            // Logout even if API call fails
        }
        api.clearToken()
        currentUser = nil
    }

    func fetchCurrentUser() async {
        isLoading = true
        defer { isLoading = false }

        do {
            let user: User = try await api.get("/auth/user")
            currentUser = user
        } catch {
            // Token invalid or expired
            api.clearToken()
            currentUser = nil
        }
    }

    // MARK: - Profile

    func updateProfile(name: String, displayName: String?, email: String, phone: String?, bio: String?) async throws {
        let response: User = try await api.put("/profile", body: UpdateProfileRequest(
            name: name,
            displayName: displayName,
            email: email,
            phone: phone,
            bio: bio
        ))
        currentUser = response
    }

    func uploadAvatar(imageData: Data) async throws {
        let response: User = try await api.upload("/profile/avatar", imageData: imageData)
        currentUser = response
    }

    func removeAvatar() async throws {
        let response: User = try await api.delete("/profile/avatar")
        currentUser = response
    }

    func changePassword(currentPassword: String, newPassword: String, confirmation: String) async throws {
        let _: MessageResponse = try await api.put("/profile/password", body: ChangePasswordRequest(
            currentPassword: currentPassword,
            password: newPassword,
            passwordConfirmation: confirmation
        ))
    }

    // MARK: - Mock (for previews and development)

    nonisolated static func mockAuthenticated() -> AuthManager {
        MainActor.assumeIsolated {
            let manager = AuthManager()
            manager.currentUser = .mock
            manager.isLoading = false
            return manager
        }
    }

    nonisolated static func mockUnauthenticated() -> AuthManager {
        MainActor.assumeIsolated {
            let manager = AuthManager()
            manager.isLoading = false
            return manager
        }
    }
}

// MARK: - Request/Response Types

private struct LoginRequest: Encodable, Sendable {
    let email: String
    let password: String
    let deviceName: String
}

private struct RegisterRequest: Encodable, Sendable {
    let name: String
    let email: String
    let password: String
    let passwordConfirmation: String
    let deviceName: String
}

struct LoginResponse: Decodable, Sendable {
    let token: String
    let user: User
}

private struct UpdateProfileRequest: Encodable, Sendable {
    let name: String
    let displayName: String?
    let email: String
    let phone: String?
    let bio: String?
}

private struct ChangePasswordRequest: Encodable, Sendable {
    let currentPassword: String
    let password: String
    let passwordConfirmation: String
}

struct MessageResponse: Decodable, Sendable {
    let message: String
}

