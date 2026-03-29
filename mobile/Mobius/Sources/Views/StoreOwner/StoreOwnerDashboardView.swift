import SwiftUI

struct StoreOwnerDashboardView: View {
    @Environment(AuthManager.self) private var auth
    @Environment(RoleManager.self) private var roleManager

    var body: some View {
        List {
            // Business Snapshot
            Section {
                HStack(spacing: 0) {
                    dashboardCell(value: "2", label: "Active Bins", icon: "trash.fill", color: .green)
                    Divider().frame(height: 50)
                    dashboardCell(value: "127", label: "Items Today", icon: "leaf.fill", color: .blue)
                    Divider().frame(height: 50)
                    dashboardCell(value: "3", label: "Pickups", icon: "shippingbox.fill", color: .orange)
                }
                .listRowInsets(EdgeInsets())
            } header: {
                Text("Business Snapshot")
            }

            // Your Outlets
            Section("Your Outlets") {
                outletRow(name: "Starbucks Gurney Plaza", bins: 2, status: "Active")
                outletRow(name: "CHAGEE Gurney Plaza", bins: 1, status: "Active")
            }

            // Alerts
            Section("Alerts") {
                HStack(spacing: 14) {
                    Image(systemName: "exclamationmark.triangle.fill")
                        .font(.system(size: 16, weight: .medium))
                        .foregroundStyle(.white)
                        .frame(width: 32, height: 32)
                        .background(.red, in: RoundedRectangle(cornerRadius: 7))

                    VStack(alignment: .leading, spacing: 2) {
                        Text("Bin MBR-2026-003 at 83%")
                            .font(.body)
                        Text("Pickup requested automatically")
                            .font(.caption)
                            .foregroundStyle(.secondary)
                    }
                }

                HStack(spacing: 14) {
                    Image(systemName: "checkmark.circle.fill")
                        .font(.system(size: 16, weight: .medium))
                        .foregroundStyle(.white)
                        .frame(width: 32, height: 32)
                        .background(.green, in: RoundedRectangle(cornerRadius: 7))

                    VStack(alignment: .leading, spacing: 2) {
                        Text("All bins operational")
                            .font(.body)
                        Text("No maintenance required")
                            .font(.caption)
                            .foregroundStyle(.secondary)
                    }
                }
            }

            // Quick Actions
            Section("Manage") {
                settingsRow(icon: "chart.line.uptrend.xyaxis", color: .purple, title: "View Analytics")
                settingsRow(icon: "doc.text.fill", color: .blue, title: "Contract Details")
                settingsRow(icon: "gift.fill", color: .pink, title: "Reward Programs")
                settingsRow(icon: "phone.fill", color: .green, title: "Contact Support")
            }
        }
        .navigationTitle("Dashboard")
        .refreshable {
            try? await Task.sleep(for: .milliseconds(500))
        }
        .toolbar {
            ToolbarItem(placement: .topBarLeading) {
                RoleHeaderButton()
            }
        }
    }

    private func dashboardCell(value: String, label: String, icon: String, color: Color) -> some View {
        VStack(spacing: 6) {
            Image(systemName: icon)
                .font(.title3)
                .foregroundStyle(color)
            Text(value)
                .font(.title2.bold())
            Text(label)
                .font(.caption)
                .foregroundStyle(.secondary)
        }
        .frame(maxWidth: .infinity)
        .padding(.vertical, 12)
    }

    private func outletRow(name: String, bins: Int, status: String) -> some View {
        HStack(spacing: 14) {
            Image(systemName: "storefront.fill")
                .font(.system(size: 16, weight: .medium))
                .foregroundStyle(.white)
                .frame(width: 32, height: 32)
                .background(.blue, in: RoundedRectangle(cornerRadius: 7))

            VStack(alignment: .leading, spacing: 2) {
                Text(name)
                    .font(.body)
                Text("\(bins) bins - \(status)")
                    .font(.caption)
                    .foregroundStyle(.secondary)
            }

            Spacer()

            Image(systemName: "chevron.right")
                .font(.caption.bold())
                .foregroundStyle(.quaternary)
        }
    }

    private func settingsRow(icon: String, color: Color, title: String) -> some View {
        HStack(spacing: 14) {
            Image(systemName: icon)
                .font(.system(size: 16, weight: .medium))
                .foregroundStyle(.white)
                .frame(width: 32, height: 32)
                .background(color, in: RoundedRectangle(cornerRadius: 7))

            Text(title)

            Spacer()

            Image(systemName: "chevron.right")
                .font(.caption.bold())
                .foregroundStyle(.quaternary)
        }
    }
}

#Preview {
    NavigationStack {
        StoreOwnerDashboardView()
    }
    .environment(AuthManager.mockAuthenticated())
    .environment(RoleManager.mockMultiRole())
}
