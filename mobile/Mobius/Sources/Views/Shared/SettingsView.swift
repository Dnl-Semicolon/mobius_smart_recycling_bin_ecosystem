import SwiftUI

struct SettingsView: View {
    @Environment(AuthManager.self) private var auth
    @State private var pushNotifications = true
    @State private var emailNotifications = false
    @AppStorage("selectedAppearance") private var selectedAppearance = 0
    @State private var showDeleteConfirmation = false

    private let appearances = ["System", "Light", "Dark"]

    var body: some View {
        List {
            // Appearance
            Section("Appearance") {
                HStack(spacing: 14) {
                    Image(systemName: "paintbrush.fill")
                        .font(.system(size: 16, weight: .medium))
                        .foregroundStyle(.white)
                        .frame(width: 32, height: 32)
                        .background(.purple, in: RoundedRectangle(cornerRadius: 7))

                    Picker("Theme", selection: $selectedAppearance) {
                        ForEach(0..<appearances.count, id: \.self) { index in
                            Text(appearances[index]).tag(index)
                        }
                    }
                }
            }

            // Notifications
            Section("Notifications") {
                Toggle(isOn: $pushNotifications) {
                    HStack(spacing: 14) {
                        Image(systemName: "bell.fill")
                            .font(.system(size: 16, weight: .medium))
                            .foregroundStyle(.white)
                            .frame(width: 32, height: 32)
                            .background(.red, in: RoundedRectangle(cornerRadius: 7))

                        Text("Push Notifications")
                    }
                }

                Toggle(isOn: $emailNotifications) {
                    HStack(spacing: 14) {
                        Image(systemName: "envelope.fill")
                            .font(.system(size: 16, weight: .medium))
                            .foregroundStyle(.white)
                            .frame(width: 32, height: 32)
                            .background(.blue, in: RoundedRectangle(cornerRadius: 7))

                        Text("Email Updates")
                    }
                }
            }

            // Language
            Section("Language") {
                settingsRow(icon: "globe", color: .cyan, title: "Language", detail: "English")
            }

            // Legal
            Section("Legal") {
                settingsRow(icon: "doc.text.fill", color: .gray, title: "Privacy Policy")
                settingsRow(icon: "doc.plaintext.fill", color: .gray, title: "Terms of Service")
            }

            // Support
            Section("Support") {
                settingsRow(icon: "questionmark.circle.fill", color: .purple, title: "Help Center")
                settingsRow(icon: "envelope.circle.fill", color: .teal, title: "Send Feedback")
            }

            // Danger zone
            Section {
                Button(role: .destructive) {
                    showDeleteConfirmation = true
                } label: {
                    HStack {
                        Spacer()
                        Text("Delete Account")
                        Spacer()
                    }
                }
            } footer: {
                Text("This will permanently delete your account and all associated data.")
            }

            // App version
            Section {
                HStack {
                    Spacer()
                    VStack(spacing: 4) {
                        Text("Mobius v1.0.0 (1)")
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
        .navigationTitle("Settings")
        .confirmationDialog(
            "Delete Account",
            isPresented: $showDeleteConfirmation,
            titleVisibility: .visible
        ) {
            Button("Delete Account", role: .destructive) {
                // Account deletion logic
            }
            Button("Cancel", role: .cancel) {}
        } message: {
            Text("Are you sure? This action cannot be undone. All your data, points, and achievements will be permanently removed.")
        }
    }

    // MARK: - Settings Row

    private func settingsRow(icon: String, color: Color, title: String, detail: String? = nil) -> some View {
        HStack(spacing: 14) {
            Image(systemName: icon)
                .font(.system(size: 16, weight: .medium))
                .foregroundStyle(.white)
                .frame(width: 32, height: 32)
                .background(color, in: RoundedRectangle(cornerRadius: 7))

            Text(title)

            Spacer()

            if let detail {
                Text(detail)
                    .font(.subheadline)
                    .foregroundStyle(.secondary)
            }

            Image(systemName: "chevron.right")
                .font(.caption.bold())
                .foregroundStyle(.quaternary)
        }
    }
}

#Preview {
    NavigationStack {
        SettingsView()
    }
    .environment(AuthManager.mockAuthenticated())
}
