import PhotosUI
import SwiftUI
import UIKit

/// Profile editing screen matching admin panel flow.
/// Avatar, name, display name, bio, phone — with read-only account info.
struct EditProfileView: View {
    @Environment(AuthManager.self) private var auth
    @Environment(RoleManager.self) private var roleManager
    @Environment(\.dismiss) private var dismiss

    @State private var name = ""
    @State private var displayName = ""
    @State private var phone = ""
    @State private var bio = ""
    @State private var selectedPhoto: PhotosPickerItem?
    @State private var avatarImage: UIImage?
    @State private var pendingAvatarData: Data?
    @State private var isSaving = false
    @State private var showRemoveAvatarConfirmation = false
    @State private var successMessage: String?
    @State private var errorMessage: String?

    private let bioLimit = 500

    var body: some View {
        Form {
            avatarSection
            profileFieldsSection
            accountInfoSection
            dangerZoneSection
        }
        .navigationTitle("Edit Profile")
        .navigationBarTitleDisplayMode(.inline)
        .toolbar {
            ToolbarItem(placement: .confirmationAction) {
                Button("Save") { save() }
                    .fontWeight(.semibold)
                    .disabled(isSaving || name.trimmingCharacters(in: .whitespaces).isEmpty)
            }
        }
        .onAppear { loadCurrentValues() }
        .onChange(of: selectedPhoto) { _, newItem in
            Task { await loadPhoto(from: newItem) }
        }
        .overlay(alignment: .bottom) {
            if let msg = successMessage {
                toast(msg, isError: false)
            } else if let msg = errorMessage {
                toast(msg, isError: true)
            }
        }
        .confirmationDialog("Remove Avatar", isPresented: $showRemoveAvatarConfirmation) {
            Button("Remove", role: .destructive) { removeAvatar() }
        } message: {
            Text("Your avatar will be replaced with your initials.")
        }
    }

    // MARK: - Sections

    private var avatarSection: some View {
        Section {
            VStack(spacing: 12) {
                ZStack(alignment: .bottomTrailing) {
                    if let img = avatarImage {
                        Image(uiImage: img)
                            .resizable()
                            .scaledToFill()
                            .frame(width: 90, height: 90)
                            .clipShape(Circle())
                    } else if let urlString = auth.currentUser?.avatarUrl, let url = URL(string: urlString) {
                        AsyncImage(url: url) { phase in
                            switch phase {
                            case .success(let image):
                                image
                                    .resizable()
                                    .scaledToFill()
                            default:
                                initialsAvatar
                            }
                        }
                        .frame(width: 90, height: 90)
                        .clipShape(Circle())
                    } else {
                        initialsAvatar
                    }

                    PhotosPicker(selection: $selectedPhoto, matching: .images) {
                        Image(systemName: "camera.fill")
                            .font(.system(size: 12, weight: .semibold))
                            .foregroundStyle(.white)
                            .frame(width: 28, height: 28)
                            .background(.green, in: Circle())
                            .overlay(Circle().stroke(.white, lineWidth: 2))
                    }
                }

                if pendingAvatarData != nil {
                    Text("New photo selected — tap Save to apply")
                        .font(.caption)
                        .foregroundStyle(.secondary)
                }

                if auth.currentUser?.avatarUrl != nil || avatarImage != nil {
                    Button("Remove Photo", role: .destructive) {
                        showRemoveAvatarConfirmation = true
                    }
                    .font(.subheadline)
                }
            }
            .frame(maxWidth: .infinity)
            .listRowBackground(Color.clear)
        }
    }

    private var initialsAvatar: some View {
        Circle()
            .fill(.green.opacity(0.15))
            .frame(width: 90, height: 90)
            .overlay {
                Text(initials)
                    .font(.title.bold())
                    .foregroundStyle(.green)
            }
    }

    private var profileFieldsSection: some View {
        Section("Profile") {
            LabeledContent("Name") {
                TextField("Full name", text: $name)
                    .multilineTextAlignment(.trailing)
            }

            LabeledContent("Display Name") {
                TextField("Optional", text: $displayName)
                    .multilineTextAlignment(.trailing)
            }

            LabeledContent("Phone") {
                TextField("+60123456789", text: $phone)
                    .multilineTextAlignment(.trailing)
                    .keyboardType(.phonePad)
            }

            VStack(alignment: .leading, spacing: 6) {
                Text("Bio")
                    .foregroundStyle(.primary)
                TextEditor(text: $bio)
                    .frame(minHeight: 80)
                    .overlay(alignment: .topLeading) {
                        if bio.isEmpty {
                            Text("Tell us about yourself...")
                                .foregroundStyle(.tertiary)
                                .padding(.top, 8)
                                .padding(.leading, 4)
                                .allowsHitTesting(false)
                        }
                    }
                Text("\(bio.count)/\(bioLimit)")
                    .font(.caption)
                    .foregroundStyle(bio.count > bioLimit ? .red : .secondary)
                    .frame(maxWidth: .infinity, alignment: .trailing)
            }
            .onChange(of: bio) { _, newValue in
                if newValue.count > bioLimit {
                    bio = String(newValue.prefix(bioLimit))
                }
            }
        }
    }

    private var accountInfoSection: some View {
        Section("Account") {
            LabeledContent("Email") {
                HStack(spacing: 6) {
                    Text(auth.currentUser?.email ?? "")
                        .foregroundStyle(.secondary)
                    Image(systemName: "checkmark.seal.fill")
                        .foregroundStyle(.green)
                        .font(.caption)
                }
            }

            LabeledContent("Roles") {
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

            if let joined = auth.currentUser?.createdAt {
                LabeledContent("Member Since") {
                    Text(joined, format: .dateTime.month(.wide).day().year())
                        .foregroundStyle(.secondary)
                }
            }
        }
    }

    private var dangerZoneSection: some View {
        Section {
            if isSaving {
                HStack {
                    Spacer()
                    ProgressView("Saving...")
                    Spacer()
                }
            }
        }
    }

    // MARK: - Actions

    private func loadCurrentValues() {
        guard let user = auth.currentUser else { return }
        name = user.name
        displayName = user.displayName ?? ""
        phone = user.phone ?? ""
        bio = user.bio ?? ""
    }

    private func save() {
        isSaving = true
        errorMessage = nil
        successMessage = nil

        Task {
            do {
                // Upload pending avatar first if user selected a new photo
                if let avatarData = pendingAvatarData {
                    try await auth.uploadAvatar(imageData: avatarData)
                    pendingAvatarData = nil
                }

                try await auth.updateProfile(
                    name: name.trimmingCharacters(in: .whitespaces),
                    displayName: displayName.isEmpty ? nil : displayName.trimmingCharacters(in: .whitespaces),
                    email: auth.currentUser?.email ?? "",
                    phone: phone.isEmpty ? nil : phone.trimmingCharacters(in: .whitespaces),
                    bio: bio.isEmpty ? nil : bio.trimmingCharacters(in: .whitespaces)
                )
                showSuccess("Profile updated")
            } catch {
                showError(error.localizedDescription)
            }
            isSaving = false
        }
    }

    private func loadPhoto(from item: PhotosPickerItem?) async {
        guard let item else { return }
        guard let data = try? await item.loadTransferable(type: Data.self) else { return }
        guard let uiImage = UIImage(data: data) else { return }

        let processed = cropToSquareAndCompress(uiImage)
        avatarImage = processed.image
        pendingAvatarData = processed.data
    }

    /**
     * Center-crop to square, resize to 512x512, compress to JPEG 0.8.
     * Matches the backend AvatarService processing.
     */
    private func cropToSquareAndCompress(_ image: UIImage) -> (image: UIImage, data: Data?) {
        let size = min(image.size.width, image.size.height)
        let x = (image.size.width - size) / 2
        let y = (image.size.height - size) / 2
        let cropRect = CGRect(x: x, y: y, width: size, height: size)

        guard let cgImage = image.cgImage?.cropping(to: cropRect) else {
            return (image, image.jpegData(compressionQuality: 0.8))
        }

        let cropped = UIImage(cgImage: cgImage, scale: image.scale, orientation: image.imageOrientation)

        let targetSize = CGSize(width: 512, height: 512)
        let renderer = UIGraphicsImageRenderer(size: targetSize)
        let resized = renderer.image { _ in
            cropped.draw(in: CGRect(origin: .zero, size: targetSize))
        }

        return (resized, resized.jpegData(compressionQuality: 0.8))
    }

    private func removeAvatar() {
        avatarImage = nil
        pendingAvatarData = nil
        Task {
            do {
                try await auth.removeAvatar()
                showSuccess("Avatar removed")
            } catch {
                showError("Failed to remove avatar")
            }
        }
    }

    private func showSuccess(_ msg: String) {
        successMessage = msg
        Task {
            try? await Task.sleep(for: .seconds(2))
            successMessage = nil
        }
    }

    private func showError(_ msg: String) {
        errorMessage = msg
        Task {
            try? await Task.sleep(for: .seconds(3))
            errorMessage = nil
        }
    }

    // MARK: - Helpers

    private var initials: String {
        let n = auth.currentUser?.name ?? "U"
        let parts = n.split(separator: " ")
        if parts.count >= 2 {
            return "\(parts[0].prefix(1))\(parts[1].prefix(1))"
        }
        return String(n.prefix(2)).uppercased()
    }

    private func toast(_ message: String, isError: Bool) -> some View {
        Text(message)
            .font(.subheadline.weight(.medium))
            .padding(.horizontal, 16)
            .padding(.vertical, 10)
            .background(isError ? Color.red : Color.green, in: Capsule())
            .foregroundStyle(.white)
            .shadow(color: .black.opacity(0.15), radius: 8, y: 4)
            .padding(.bottom, 20)
            .transition(.move(edge: .bottom).combined(with: .opacity))
            .animation(.spring(duration: 0.3), value: successMessage)
            .animation(.spring(duration: 0.3), value: errorMessage)
    }
}

#Preview {
    NavigationStack {
        EditProfileView()
    }
    .environment(AuthManager.mockAuthenticated())
    .environment(RoleManager.mockMultiRole())
}
