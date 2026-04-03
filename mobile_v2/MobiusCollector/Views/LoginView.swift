import SwiftUI

struct LoginView: View {
    @Environment(AuthViewModel.self) private var authVM
    @FocusState private var focusedField: Field?

    private enum Field { case email, password }

    var body: some View {
        @Bindable var auth = authVM

        VStack(spacing: 32) {
            Spacer()

            // Logo
            VStack(spacing: 8) {
                Image(systemName: "arrow.3.trianglepath")
                    .font(.system(size: 60))
                    .foregroundStyle(Color.mobiusTeal)

                Text("Mobius")
                    .font(.largeTitle.bold())

                Text("Collector")
                    .font(.title3)
                    .foregroundStyle(.secondary)
            }

            // Form
            VStack(spacing: 16) {
                TextField("Email", text: $auth.email)
                    .textFieldStyle(.roundedBorder)
                    .textContentType(.emailAddress)
                    .autocorrectionDisabled()
                    .textInputAutocapitalization(.never)
                    .keyboardType(.emailAddress)
                    .focused($focusedField, equals: .email)

                SecureField("Password", text: $auth.password)
                    .textFieldStyle(.roundedBorder)
                    .textContentType(.password)
                    .focused($focusedField, equals: .password)

                if let error = authVM.errorMessage {
                    Text(error)
                        .font(.caption)
                        .foregroundStyle(.red)
                        .multilineTextAlignment(.center)
                }

                Button {
                    focusedField = nil
                    Task { await authVM.login() }
                } label: {
                    Group {
                        if authVM.isLoading {
                            ProgressView()
                        } else {
                            Text("Sign In")
                        }
                    }
                    .font(.headline)
                    .frame(maxWidth: .infinity)
                    .frame(height: 50)
                }
                .buttonStyle(.borderedProminent)
                .tint(Color.mobiusTeal)
                .disabled(authVM.isLoading || authVM.email.isEmpty || authVM.password.isEmpty)
            }
            .padding(.horizontal, 32)

            Spacer()
            Spacer()
        }
    }
}
