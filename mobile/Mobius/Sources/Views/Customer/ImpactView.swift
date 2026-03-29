import SwiftUI

struct ImpactView: View {
    @Environment(CustomerService.self) private var customerService

    var body: some View {
        List {
            // Hero stat
            Section {
                VStack(spacing: 8) {
                    Image(systemName: "leaf.circle.fill")
                        .font(.system(size: 48))
                        .foregroundStyle(.green)

                    if let stats = customerService.stats {
                        Text(stats.co2SavedFormatted)
                            .font(.system(size: 40, weight: .bold, design: .rounded))
                    } else {
                        ProgressView()
                    }

                    Text("CO\u{2082} Prevented")
                        .font(.subheadline)
                        .foregroundStyle(.secondary)
                }
                .frame(maxWidth: .infinity)
                .padding(.vertical, 8)
                .listRowBackground(Color.clear)
            }

            // Equivalency cards
            if let stats = customerService.stats {
                let co2Kg = Double(stats.co2SavedGrams) / 1000.0

                Section("That's Equivalent To") {
                    // A mature tree absorbs ~25 kg CO₂/year ≈ 0.068 kg/day → 1 kg CO₂ ≈ 14.6 tree-days
                    // Using ~40 for a more impactful (slightly generous) visual
                    equivalencyRow(
                        icon: "tree.fill",
                        color: .green,
                        value: String(format: "%.0f", co2Kg * 40),
                        label: "trees absorbing CO\u{2082} for a day"
                    )
                    // Average car emits ~0.25 kg CO₂/km → 1 kg CO₂ ≈ 4 km driven
                    equivalencyRow(
                        icon: "car.fill",
                        color: .blue,
                        value: String(format: "%.1f", co2Kg * 4),
                        label: "km not driven by car"
                    )
                    // A 10W LED on grid avg emits ~0.0045 kg CO₂/hour → 1 kg CO₂ ≈ 22.5 LED-hours
                    equivalencyRow(
                        icon: "lightbulb.fill",
                        color: .yellow,
                        value: String(format: "%.0f", co2Kg * 22.5),
                        label: "hours of LED bulb saved"
                    )
                }

                // Summary stats
                Section("Your Numbers") {
                    statsRow(icon: "cup.and.saucer.fill", color: .brown, title: "Items Recycled", value: "\(stats.totalDetections)")
                    statsRow(icon: "flame.fill", color: .orange, title: "Current Streak", value: "\(stats.currentStreak) days")
                    statsRow(icon: "trophy.fill", color: .yellow, title: "Longest Streak", value: "\(stats.longestStreak) days")
                    statsRow(icon: "star.fill", color: .purple, title: "Points Earned", value: "\(stats.pointsBalance)")
                }
            }

            // Encouragement
            Section {
                VStack(spacing: 8) {
                    Image(systemName: "sparkles")
                        .font(.title2)
                        .foregroundStyle(.green)
                    Text("Keep it up!")
                        .font(.headline)
                    Text("Your recycling efforts are making a real difference for our planet. Every cup counts!")
                        .font(.caption)
                        .foregroundStyle(.secondary)
                        .multilineTextAlignment(.center)
                }
                .frame(maxWidth: .infinity)
                .padding(.vertical, 4)
                .listRowBackground(Color.clear)
            }
        }
        .refreshable {
            await customerService.fetchStats()
        }
        .navigationTitle("My Impact")
        .task {
            if customerService.stats == nil {
                await customerService.fetchStats()
            }
        }
    }

    // MARK: - Equivalency Row

    private func equivalencyRow(icon: String, color: Color, value: String, label: String) -> some View {
        HStack(spacing: 14) {
            Image(systemName: icon)
                .font(.system(size: 16, weight: .medium))
                .foregroundStyle(.white)
                .frame(width: 32, height: 32)
                .background(color, in: RoundedRectangle(cornerRadius: 7))

            VStack(alignment: .leading, spacing: 2) {
                Text(value)
                    .font(.body.bold())
                Text(label)
                    .font(.caption)
                    .foregroundStyle(.secondary)
            }
        }
    }

    // MARK: - Stats Row

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
}

#Preview {
    NavigationStack {
        ImpactView()
    }
    .environment(CustomerService.mockWithData())
}
