import SwiftUI

/// Change password screen — current password + new password with confirmation.
struct ChangePasswordView: View {
    @Environment(AuthManager.self) private var auth
    @Environment(\.dismiss) private var dismiss

    @State private var currentPassword = ""
    @State private var newPassword = ""
    @State private var confirmPassword = ""
    @State private var isSaving = false
    @State private var errorMessage: String?
    @State private var showSuccess = false

    private var isValid: Bool {
        !currentPassword.isEmpty && newPassword.count >= 8 && newPassword == confirmPassword
    }

    var body: some View {
        Form {
            Section {
                SecureField("Current Password", text: $currentPassword)
            } footer: {
                Text("Enter your existing password to verify your identity.")
            }

            Section {
                SecureField("New Password", text: $newPassword)
                SecureField("Confirm New Password", text: $confirmPassword)
            } footer: {
                VStack(alignment: .leading, spacing: 4) {
                    requirement("At least 8 characters", met: newPassword.count >= 8)
                    requirement("Passwords match", met: !confirmPassword.isEmpty && newPassword == confirmPassword)
                }
            }

            if let error = errorMessage {
                Section {
                    Label(error, systemImage: "exclamationmark.triangle.fill")
                        .foregroundStyle(.red)
                        .font(.subheadline)
                }
            }

            Section {
                Button {
                    save()
                } label: {
                    HStack {
                        Spacer()
                        if isSaving {
                            ProgressView()
                        } else {
                            Text("Update Password")
                                .fontWeight(.semibold)
                        }
                        Spacer()
                    }
                }
                .disabled(!isValid || isSaving)
            }
        }
        .navigationTitle("Change Password")
        .navigationBarTitleDisplayMode(.inline)
        .alert("Password Updated", isPresented: $showSuccess) {
            Button("OK") { dismiss() }
        } message: {
            Text("Your password has been changed successfully.")
        }
    }

    private func save() {
        isSaving = true
        errorMessage = nil

        Task {
            do {
                try await auth.changePassword(
                    currentPassword: currentPassword,
                    newPassword: newPassword,
                    confirmation: confirmPassword
                )
                HapticManager.notification(.success)
                showSuccess = true
            } catch {
                errorMessage = error.localizedDescription
            }
            isSaving = false
        }
    }

    private func requirement(_ text: String, met: Bool) -> some View {
        HStack(spacing: 4) {
            Image(systemName: met ? "checkmark.circle.fill" : "circle")
                .foregroundStyle(met ? .green : .secondary)
                .font(.caption)
            Text(text)
        }
    }
}

#Preview {
    NavigationStack {
        ChangePasswordView()
    }
    .environment(AuthManager.mockAuthenticated())
}
