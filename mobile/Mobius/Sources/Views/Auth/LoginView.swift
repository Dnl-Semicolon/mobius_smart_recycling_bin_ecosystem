import SwiftUI

struct LoginView: View {
    @Environment(AuthManager.self) private var auth

    @State private var email = ""
    @State private var password = ""

    var body: some View {
        ScrollView {
            VStack(spacing: 32) {
                // Logo
                VStack(spacing: 8) {
                    Image("MobiusIcon")
                        .resizable()
                        .scaledToFit()
                        .frame(height: 72)

                    Image("MobiusWordmark")
                        .resizable()
                        .scaledToFit()
                        .frame(height: 28)

                    Text("Smart Recycling Ecosystem")
                        .font(.subheadline)
                        .foregroundStyle(.secondary)
                }
                .padding(.top, 60)

                // Form
                VStack(spacing: 16) {
                    TextField("Email", text: $email)
                        .textContentType(.emailAddress)
                        .keyboardType(.emailAddress)
                        .autocorrectionDisabled()
                        .textInputAutocapitalization(.never)
                        .padding()
                        .background(Color(.secondarySystemBackground))
                        .clipShape(RoundedRectangle(cornerRadius: 12))

                    SecureField("Password", text: $password)
                        .textContentType(.password)
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
                        Task { await auth.login(email: email, password: password) }
                    } label: {
                        if auth.isLoading {
                            ProgressView()
                                .frame(maxWidth: .infinity)
                                .frame(height: 50)
                        } else {
                            Text("Sign In")
                                .fontWeight(.semibold)
                                .frame(maxWidth: .infinity)
                                .frame(height: 50)
                        }
                    }
                    .buttonStyle(.borderedProminent)
                    .tint(.green)
                    .controlSize(.large)
                    .disabled(email.isEmpty || password.isEmpty || auth.isLoading)
                }
                .padding(.horizontal, 24)

                // Register link
                NavigationLink {
                    RegisterView()
                } label: {
                    HStack(spacing: 4) {
                        Text("Don't have an account?")
                            .foregroundStyle(.secondary)
                        Text("Sign Up")
                            .fontWeight(.semibold)
                            .foregroundStyle(.green)
                    }
                    .font(.subheadline)
                }

                #if DEBUG
                // Demo mode — skip API, load mock data
                Divider()
                    .padding(.horizontal, 40)

                Button {
                    auth.loginDemo()
                } label: {
                    Label("Enter Demo Mode", systemImage: "play.fill")
                        .font(.subheadline.weight(.medium))
                        .frame(maxWidth: .infinity)
                }
                .buttonStyle(.bordered)
                .tint(.orange)
                .controlSize(.large)
                .padding(.horizontal, 24)
                #endif
            }
        }
        .navigationBarHidden(true)
    }
}

#Preview {
    NavigationStack {
        LoginView()
    }
    .environment(AuthManager.mockUnauthenticated())
}
