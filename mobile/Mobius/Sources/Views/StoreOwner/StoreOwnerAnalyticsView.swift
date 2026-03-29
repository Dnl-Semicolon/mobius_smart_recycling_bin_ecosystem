import SwiftUI

struct StoreOwnerAnalyticsView: View {
    var body: some View {
        List {
            Section("This Week") {
                statsRow(icon: "leaf.fill", color: .green, title: "Items Recycled", value: "234")
                statsRow(icon: "shippingbox.fill", color: .orange, title: "Pickups Completed", value: "5")
                statsRow(icon: "scalemass.fill", color: .purple, title: "Est. Weight", value: "~8.2 kg")
                statsRow(icon: "cloud.fill", color: .teal, title: "CO₂ Prevented", value: "5.6 kg")
            }

            Section("This Month") {
                statsRow(icon: "leaf.fill", color: .green, title: "Items Recycled", value: "892")
                statsRow(icon: "shippingbox.fill", color: .orange, title: "Pickups Completed", value: "18")
                statsRow(icon: "person.2.fill", color: .blue, title: "Unique Recyclers", value: "67")
            }

            Section("Top Waste Types") {
                wasteRow(type: .paperCup, count: 412, percentage: 46)
                wasteRow(type: .plasticCup, count: 234, percentage: 26)
                wasteRow(type: .lid, count: 156, percentage: 17)
                wasteRow(type: .straw, count: 90, percentage: 11)
            }
        }
        .navigationTitle("Analytics")
        .refreshable {
            try? await Task.sleep(for: .milliseconds(500))
        }
    }

    private func statsRow(icon: String, color: Color, title: String, value: String) -> some View {
        HStack(spacing: 14) {
            Image(systemName: icon)
                .font(.system(size: 16, weight: .medium))
                .foregroundStyle(.white)
                .frame(width: 32, height: 32)
                .background(color, in: RoundedRectangle(cornerRadius: 7))

            Text(title)

            Spacer()

            Text(value)
                .font(.subheadline.bold())
                .foregroundStyle(.secondary)
        }
    }

    private func wasteRow(type: WasteType, count: Int, percentage: Int) -> some View {
        HStack(spacing: 14) {
            Image(systemName: type.icon)
                .font(.system(size: 16, weight: .medium))
                .foregroundStyle(.white)
                .frame(width: 32, height: 32)
                .background(type.iconColor, in: RoundedRectangle(cornerRadius: 7))

            Text(type.label)

            Spacer()

            VStack(alignment: .trailing, spacing: 2) {
                Text("\(count)")
                    .font(.subheadline.bold())
                Text("\(percentage)%")
                    .font(.caption)
                    .foregroundStyle(.secondary)
            }
        }
    }
}

#Preview {
    NavigationStack {
        StoreOwnerAnalyticsView()
    }
}
