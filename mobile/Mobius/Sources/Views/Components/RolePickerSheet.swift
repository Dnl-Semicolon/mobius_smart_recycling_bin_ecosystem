import SwiftUI

/// Settings-app style role picker shown as a bottom sheet.
/// Vertical list with colored icons — exactly the UI pattern you love.
struct RolePickerSheet: View {
    @Environment(RoleManager.self) private var roleManager
    @Environment(\.dismiss) private var dismiss

    var body: some View {
        NavigationStack {
            List {
                Section {
                    ForEach(roleManager.availableRoles) { role in
                        Button {
                            roleManager.switchTo(role)
                            dismiss()
                        } label: {
                            HStack(spacing: 14) {
                                // Colored icon with rounded square background (Settings-app style)
                                Image(systemName: role.icon)
                                    .font(.system(size: 16, weight: .medium))
                                    .foregroundStyle(.white)
                                    .frame(width: 32, height: 32)
                                    .background(role.iconColor, in: RoundedRectangle(cornerRadius: 7))

                                VStack(alignment: .leading, spacing: 2) {
                                    Text(role.label)
                                        .font(.body)
                                        .foregroundStyle(.primary)

                                    Text(role.description)
                                        .font(.caption)
                                        .foregroundStyle(.secondary)
                                }

                                Spacer()

                                if roleManager.activeRole == role {
                                    Image(systemName: "checkmark")
                                        .font(.body.bold())
                                        .foregroundStyle(.green)
                                }
                            }
                            .contentShape(Rectangle())
                        }
                        .buttonStyle(.plain)
                    }
                } header: {
                    Text("Switch Mode")
                } footer: {
                    Text("Your account supports multiple roles. Switch between them without logging out.")
                }
            }
            .navigationTitle("Switch Role")
            .navigationBarTitleDisplayMode(.inline)
            .toolbar {
                ToolbarItem(placement: .topBarTrailing) {
                    Button("Done") { dismiss() }
                }
            }
        }
        .presentationDetents([.medium])
        .presentationDragIndicator(.visible)
    }
}

// MARK: - Role Header Button (used in navigation bars)

struct RoleHeaderButton: View {
    @Environment(RoleManager.self) private var roleManager

    var body: some View {
        if roleManager.canSwitchRoles {
            Button {
                roleManager.isShowingRolePicker = true
            } label: {
                HStack(spacing: 6) {
                    Image(systemName: roleManager.activeRole.icon)
                        .font(.system(size: 12, weight: .medium))
                        .foregroundStyle(.white)
                        .frame(width: 24, height: 24)
                        .background(roleManager.activeRole.iconColor, in: RoundedRectangle(cornerRadius: 6))

                    Text(roleManager.activeRole.label)
                        .font(.subheadline.weight(.medium))

                    Image(systemName: "chevron.down")
                        .font(.caption2.bold())
                        .foregroundStyle(.secondary)
                }
            }
            .buttonStyle(.plain)
            .sheet(isPresented: Binding(
                get: { roleManager.isShowingRolePicker },
                set: { roleManager.isShowingRolePicker = $0 }
            )) {
                RolePickerSheet()
            }
        }
    }
}

#Preview {
    RolePickerSheet()
        .environment(RoleManager.mockMultiRole())
}
