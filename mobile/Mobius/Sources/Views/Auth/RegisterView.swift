import SwiftUI

struct RegisterView: View {
    @Environment(AuthManager.self) private var auth
    @Environment(\.dismiss) private var dismiss

    @State private var name = ""
    @State private var email = ""
    @State private var password = ""
    @State private var confirmPassword = ""

    var body: some View {
        ScrollView {
            VStack(spacing: 24) {
                VStack(spacing: 4) {
                    Text("Create Account")
                        .font(.title.bold())
                    Text("Join the recycling ecosystem")
                        .font(.subheadline)
                        .foregroundStyle(.secondary)
                }
                .padding(.top, 24)

                VStack(spacing: 16) {
                    TextField("Full Name", text: $name)
                        .textContentType(.name)
                        .padding()
                        .background(Color(.secondarySystemBackground))
                        .clipShape(RoundedRectangle(cornerRadius: 12))

                    TextField("Email", text: $email)
                        .textContentType(.emailAddress)
                        .keyboardType(.emailAddress)
                        .autocorrectionDisabled()
                        .textInputAutocapitalization(.never)
                        .padding()
                        .background(Color(.secondarySystemBackground))
                        .clipShape(RoundedRectangle(cornerRadius: 12))

                    SecureField("Password", text: $password)
                        .textContentType(.newPassword)
                        .padding()
                        .background(Color(.secondarySystemBackground))
                        .clipShape(RoundedRectangle(cornerRadius: 12))

                    SecureField("Confirm Password", text: $confirmPassword)
                        .textContentType(.newPassword)
                        .padding()
                        .background(Color(.secondarySystemBackground))
                        .clipShape(RoundedRectangle(cornerRadius: 12))

                    if let error = auth.error {
                        Text(error)
                            .font(.caption)
                            .foregroundStyle(.red)
                            .frame(maxWidth: .infinity, alignment: .leading)
                    }

                    Button {
                        HapticManager.impact(.medium)
                        Task {
                            await auth.register(
                                name: name,
                                email: email,
                                password: password,
                                passwordConfirmation: confirmPassword
                            )
                        }
                    } label: {
                        if auth.isLoading {
                            ProgressView()
                                .frame(maxWidth: .infinity)
                                .frame(height: 50)
                        } else {
                            Text("Create Account")
                                .fontWeight(.semibold)
                                .frame(maxWidth: .infinity)
                                .frame(height: 50)
                        }
                    }
                    .buttonStyle(.borderedProminent)
                    .tint(.green)
                    .controlSize(.large)
                    .disabled(!isFormValid || auth.isLoading)
                }
                .padding(.horizontal, 24)
            }
        }
        .navigationTitle("Sign Up")
        .navigationBarTitleDisplayMode(.inline)
    }

    private var isFormValid: Bool {
        !name.isEmpty && !email.isEmpty && !password.isEmpty && password == confirmPassword
    }
}

#Preview {
    NavigationStack {
        RegisterView()
    }
    .environment(AuthManager.mockUnauthenticated())
}
