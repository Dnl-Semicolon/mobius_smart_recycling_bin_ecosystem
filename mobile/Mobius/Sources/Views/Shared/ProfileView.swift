import SwiftUI

/// Shared profile view used across all roles.
/// Settings-app style layout with colored icons.
struct ProfileView: View {
    @Environment(AuthManager.self) private var auth
    @Environment(RoleManager.self) private var roleManager
    @Environment(NotificationManager.self) private var notificationManager
    @State private var showAvatarFullscreen = false
    @State private var showSignOutConfirmation = false

    var body: some View {
        List {
            // Profile header
            Section {
                HStack(spacing: 14) {
                    avatarView
                        .onTapGesture {
                            if auth.currentUser?.avatarUrl != nil {
                                showAvatarFullscreen = true
                            }
                        }
                    VStack(alignment: .leading, spacing: 4) {
                        Text(auth.currentUser?.displayName ?? auth.currentUser?.name ?? "User")
                            .font(.title3.bold())
                        Text(auth.currentUser?.email ?? "")
                            .font(.subheadline)
                            .foregroundStyle(.secondary)
                        if roleManager.canSwitchRoles {
                            HStack(spacing: 4) {
                                ForEach(roleManager.availableRoles) { role in
                                    Text(role.label)
                                        .font(.caption2)
                                        .padding(.horizontal, 6)
                                        .padding(.vertical, 2)
                                        .background(role.iconColor.opacity(0.15), in: Capsule())
                                        .foregroundStyle(role.iconColor)
                                }
                            }
                        }
                    }
                }
            }

            // Account section
            Section("Account") {
                NavigationLink {
                    EditProfileView()
                } label: {
                    settingsRow(icon: "person.fill", color: .blue, title: "Edit Profile", showChevron: false)
                }

                NavigationLink {
                    ChangePasswordView()
                } label: {
                    settingsRow(icon: "lock.fill", color: .gray, title: "Change Password", showChevron: false)
                }

                if roleManager.canSwitchRoles {
                    Button {
                        roleManager.isShowingRolePicker = true
                    } label: {
                        HStack(spacing: 14) {
                            Image(systemName: "arrow.triangle.2.circlepath")
                                .font(.system(size: 16, weight: .medium))
                                .foregroundStyle(.white)
                                .frame(width: 32, height: 32)
                                .background(.orange, in: RoundedRectangle(cornerRadius: 7))

                            Text("Switch Role")
                                .foregroundStyle(.primary)

                            Spacer()

                            Text(roleManager.activeRole.label)
                                .font(.subheadline)
                                .foregroundStyle(.secondary)

                            Image(systemName: "chevron.right")
                                .font(.caption.bold())
                                .foregroundStyle(.quaternary)
                        }
                    }
                    .buttonStyle(.plain)
                }
            }

            // Activity section (Customer only)
            if roleManager.activeRole == .publicUser {
                Section("Activity") {
                    settingsRow(icon: "chart.bar.fill", color: .blue, title: "My Analytics")
                    settingsRow(icon: "trophy.fill", color: .yellow, title: "Achievements")
                    settingsRow(icon: "flame.fill", color: .orange, title: "Challenges")
                    settingsRow(icon: "person.2.fill", color: .green, title: "Friends")
                }
            }

            // Preferences
            Section("Preferences") {
                NavigationLink {
                    NotificationsView()
                } label: {
                    HStack(spacing: 14) {
                        Image(systemName: "bell.fill")
                            .font(.system(size: 16, weight: .medium))
                            .foregroundStyle(.white)
                            .frame(width: 32, height: 32)
                            .background(.red, in: RoundedRectangle(cornerRadius: 7))

                        Text("Notifications")

                        Spacer()

                        if notificationManager.unreadCount > 0 {
                            Text("\(notificationManager.unreadCount)")
                                .font(.caption2.bold())
                                .foregroundStyle(.white)
                                .padding(.horizontal, 6)
                                .padding(.vertical, 2)
                                .background(.red, in: Capsule())
                        }
                    }
                }
                settingsRow(icon: "globe", color: .blue, title: "Language")
                settingsRow(icon: "questionmark.circle.fill", color: .purple, title: "Help & Support")
            }

            // Sign out
            Section {
                Button(role: .destructive) {
                    showSignOutConfirmation = true
                } label: {
                    HStack {
                        Spacer()
                        Text("Sign Out")
                        Spacer()
                    }
                }
                .confirmationDialog("Are you sure you want to sign out?", isPresented: $showSignOutConfirmation, titleVisibility: .visible) {
                    Button("Sign Out", role: .destructive) {
                        Task { await auth.logout() }
                    }
                }
            }

            // App info
            Section {
                HStack {
                    Spacer()
                    VStack(spacing: 4) {
                        Text("Mobius v1.0.0")
                            .font(.caption)
                            .foregroundStyle(.secondary)
                        Text("Smart Recycling Ecosystem")
                            .font(.caption2)
                            .foregroundStyle(.tertiary)
                    }
                    Spacer()
                }
                .listRowBackground(Color.clear)
            }
        }
        .navigationTitle("Profile")
        .fullScreenCover(isPresented: $showAvatarFullscreen) {
            AvatarFullscreenView(
                avatarUrl: auth.currentUser?.avatarUrl,
                name: auth.currentUser?.displayName ?? auth.currentUser?.name ?? "User"
            )
        }
    }

    // MARK: - Avatar

    @ViewBuilder
    private var avatarView: some View {
        if let urlString = auth.currentUser?.avatarUrl, let url = URL(string: urlString) {
            AsyncImage(url: url) { phase in
                switch phase {
                case .success(let image):
                    image
                        .resizable()
                        .scaledToFill()
                        .frame(width: 60, height: 60)
                        .clipShape(Circle())
                default:
                    initialsCircle
                }
            }
            .frame(width: 60, height: 60)
        } else {
            initialsCircle
        }
    }

    private var initialsCircle: some View {
        Circle()
            .fill(.green.opacity(0.15))
            .frame(width: 60, height: 60)
            .overlay {
                Text(initials)
                    .font(.title2.bold())
                    .foregroundStyle(.green)
            }
    }

    // MARK: - Helpers

    private var initials: String {
        let name = auth.currentUser?.name ?? "U"
        let parts = name.split(separator: " ")
        if parts.count >= 2 {
            return "\(parts[0].prefix(1))\(parts[1].prefix(1))"
        }
        return String(name.prefix(2)).uppercased()
    }

    private func settingsRow(icon: String, color: Color, title: String, showChevron: Bool = true) -> some View {
        HStack(spacing: 14) {
            Image(systemName: icon)
                .font(.system(size: 16, weight: .medium))
                .foregroundStyle(.white)
                .frame(width: 32, height: 32)
                .background(color, in: RoundedRectangle(cornerRadius: 7))

            Text(title)

            Spacer()

            if showChevron {
                Image(systemName: "chevron.right")
                    .font(.caption.bold())
                    .foregroundStyle(.quaternary)
            }
        }
    }
}

#Preview {
    NavigationStack {
        ProfileView()
    }
    .environment(AuthManager.mockAuthenticated())
    .environment(RoleManager.mockMultiRole())
    .environment(NotificationManager.mockWithNotifications())
}
