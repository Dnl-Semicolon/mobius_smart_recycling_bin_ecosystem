import SwiftUI

struct CollectorStatsView: View {
    @Environment(CollectorService.self) private var collectorService

    var body: some View {
        List {
            if let stats = collectorService.stats {
                Section("Performance") {
                    statsRow(icon: "checkmark.circle.fill", color: .green, title: "Total Completed", value: "\(stats.totalCompleted)")
                    statsRow(icon: "calendar", color: .blue, title: "This Week", value: "\(stats.thisWeek)")
                    statsRow(icon: "bolt.fill", color: .orange, title: "Active Now", value: "\(stats.activeNow)")
                    if let avg = stats.avgMinutes {
                        statsRow(icon: "clock.fill", color: .purple, title: "Avg Response Time", value: "\(avg) min")
                    }
                }
            } else if collectorService.isLoadingStats {
                Section {
                    HStack {
                        Spacer()
                        ProgressView("Loading stats...")
                        Spacer()
                    }
                }
            } else {
                Section {
                    ContentUnavailableView(
                        "No Stats Yet",
                        systemImage: "chart.bar.fill",
                        description: Text("Complete some pickups to see your performance stats.")
                    )
                    .listRowBackground(Color.clear)
                }
            }
        }
        .refreshable {
            await collectorService.fetchStats()
        }
        .navigationTitle("Stats")
        .task {
            if collectorService.stats == nil {
                await collectorService.fetchStats()
            }
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
}

#Preview {
    NavigationStack {
        CollectorStatsView()
    }
    .environment(CollectorService.mockWithData())
}
