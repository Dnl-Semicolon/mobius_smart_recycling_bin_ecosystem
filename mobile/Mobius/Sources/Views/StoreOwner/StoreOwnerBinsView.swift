import SwiftUI

struct StoreOwnerBinsView: View {
    var body: some View {
        List {
            Section("Starbucks Gurney Plaza") {
                binRow(serial: "MBR-2026-001", fill: 45, status: .active)
                binRow(serial: "MBR-2026-002", fill: 12, status: .active)
            }

            Section("CHAGEE Gurney Plaza") {
                binRow(serial: "MBR-2026-003", fill: 83, status: .active)
            }
        }
        .navigationTitle("My Bins")
        .refreshable {
            try? await Task.sleep(for: .milliseconds(500))
        }
    }

    private func binRow(serial: String, fill: Int, status: BinStatus) -> some View {
        HStack(spacing: 14) {
            // Fill level indicator
            ZStack {
                Circle()
                    .stroke(.gray.opacity(0.2), lineWidth: 5)
                Circle()
                    .trim(from: 0, to: Double(fill) / 100.0)
                    .stroke(fillColor(fill), style: StrokeStyle(lineWidth: 5, lineCap: .round))
                    .rotationEffect(.degrees(-90))
                Text("\(fill)%")
                    .font(.caption2.bold())
            }
            .frame(width: 48, height: 48)

            VStack(alignment: .leading, spacing: 2) {
                Text(serial)
                    .font(.body.monospaced())
                Text(status.label)
                    .font(.caption)
                    .foregroundStyle(.secondary)
            }

            Spacer()

            if fill >= 80 {
                Text("Needs Pickup")
                    .font(.caption.bold())
                    .padding(.horizontal, 8)
                    .padding(.vertical, 4)
                    .background(.red.opacity(0.15), in: Capsule())
                    .foregroundStyle(.red)
            }

            Image(systemName: "chevron.right")
                .font(.caption.bold())
                .foregroundStyle(.quaternary)
        }
    }

    private func fillColor(_ fill: Int) -> Color {
        if fill >= 80 { return .red }
        if fill >= 50 { return .orange }
        return .green
    }
}

#Preview {
    NavigationStack {
        StoreOwnerBinsView()
    }
}
