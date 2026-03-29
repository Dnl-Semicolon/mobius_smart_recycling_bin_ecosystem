import SwiftUI

/// Manages the user's active role selection.
/// When a user has multiple roles, this controls which "mode" the app is in.
@MainActor
@Observable
final class RoleManager {
    /// The currently active role determining which tab layout is shown
    var activeRole: UserRole = .publicUser

    /// Whether the role picker sheet is showing
    var isShowingRolePicker = false

    /// Available roles for the current user
    var availableRoles: [UserRole] = []

    /// Whether the user can switch roles
    var canSwitchRoles: Bool {
        availableRoles.count > 1
    }

    // MARK: - Setup

    func configure(for user: User) {
        availableRoles = user.roles.filter { UserRole.mobileRoles.contains($0) }
        // Default to their primary role, or stay on current if valid
        if !availableRoles.contains(activeRole) {
            activeRole = availableRoles.first ?? .publicUser
        }
    }

    // MARK: - Role Switching

    func switchTo(_ role: UserRole) {
        guard availableRoles.contains(role) else { return }
        withAnimation(.easeInOut(duration: 0.3)) {
            activeRole = role
        }
        isShowingRolePicker = false
        HapticManager.impact(.medium)
    }

    // MARK: - Mock

    nonisolated static func mockMultiRole() -> RoleManager {
        MainActor.assumeIsolated {
            let manager = RoleManager()
            manager.availableRoles = [.publicUser, .collector, .storeOwner]
            manager.activeRole = .publicUser
            return manager
        }
    }

    nonisolated static func mockSingleRole() -> RoleManager {
        MainActor.assumeIsolated {
            let manager = RoleManager()
            manager.availableRoles = [.publicUser]
            manager.activeRole = .publicUser
            return manager
        }
    }
}
