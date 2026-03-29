import SwiftUI

struct AchievementsView: View {
    private let achievements = Achievement.mockData

    private var unlockedCount: Int {
        achievements.filter(\.isUnlocked).count
    }

    var body: some View {
        List {
            // Progress header
            Section {
                VStack(spacing: 12) {
                    Text("\(unlockedCount)/\(achievements.count)")
                        .font(.system(size: 32, weight: .bold, design: .rounded))

                    Text("Achievements Unlocked")
                        .font(.subheadline)
                        .foregroundStyle(.secondary)

                    ProgressView(value: Double(unlockedCount), total: Double(achievements.count))
                        .tint(.green)
                }
                .frame(maxWidth: .infinity)
                .padding(.vertical, 4)
                .listRowBackground(Color.clear)
            }

            // Unlocked
            Section("Unlocked") {
                ForEach(achievements.filter(\.isUnlocked)) { achievement in
                    achievementRow(achievement)
                }
            }

            // Locked
            Section("Locked") {
                ForEach(achievements.filter { !$0.isUnlocked }) { achievement in
                    achievementRow(achievement)
                }
            }
        }
        .navigationTitle("Achievements")
        .refreshable {
            try? await Task.sleep(for: .milliseconds(500))
        }
    }

    // MARK: - Achievement Row

    private func achievementRow(_ achievement: Achievement) -> some View {
        HStack(spacing: 14) {
            Image(systemName: achievement.icon)
                .font(.system(size: 16, weight: .medium))
                .foregroundStyle(.white)
                .frame(width: 32, height: 32)
                .background(
                    achievement.isUnlocked ? achievement.color : .gray,
                    in: RoundedRectangle(cornerRadius: 7)
                )
                .opacity(achievement.isUnlocked ? 1.0 : 0.35)

            VStack(alignment: .leading, spacing: 2) {
                Text(achievement.title)
                    .font(.body)
                    .foregroundStyle(achievement.isUnlocked ? .primary : .secondary)
                Text(achievement.description)
                    .font(.caption)
                    .foregroundStyle(.secondary)
                Text("\(achievement.pointsReward) pts reward")
                    .font(.caption2)
                    .foregroundStyle(achievement.isUnlocked ? .green : .secondary)
            }

            Spacer()

            if achievement.isUnlocked {
                Image(systemName: "checkmark.circle.fill")
                    .foregroundStyle(.green)
            } else {
                Image(systemName: "lock.fill")
                    .foregroundStyle(.quaternary)
            }
        }
    }
}

// MARK: - Achievement Model

private struct Achievement: Identifiable {
    let id: String
    let icon: String
    let color: Color
    let title: String
    let description: String
    let pointsReward: Int
    let isUnlocked: Bool

    static let mockData: [Achievement] = [
        Achievement(
            id: "first_steps",
            icon: "leaf.fill",
            color: .green,
            title: "First Steps",
            description: "Recycle your first cup",
            pointsReward: 50,
            isUnlocked: true
        ),
        Achievement(
            id: "week_warrior",
            icon: "flame.fill",
            color: .orange,
            title: "Week Warrior",
            description: "Maintain a 7-day recycling streak",
            pointsReward: 100,
            isUnlocked: true
        ),
        Achievement(
            id: "half_century",
            icon: "cup.and.saucer.fill",
            color: .brown,
            title: "Half Century",
            description: "Recycle 50 cups",
            pointsReward: 200,
            isUnlocked: true
        ),
        Achievement(
            id: "eco_champion",
            icon: "globe.americas.fill",
            color: .blue,
            title: "Eco Champion",
            description: "Prevent 5 kg of CO\u{2082} emissions",
            pointsReward: 500,
            isUnlocked: false
        ),
        Achievement(
            id: "century_club",
            icon: "star.circle.fill",
            color: .yellow,
            title: "Century Club",
            description: "Recycle 100 cups",
            pointsReward: 300,
            isUnlocked: false
        ),
        Achievement(
            id: "month_master",
            icon: "calendar.badge.checkmark",
            color: .purple,
            title: "Month Master",
            description: "Maintain a 30-day streak",
            pointsReward: 400,
            isUnlocked: false
        ),
        Achievement(
            id: "social_recycler",
            icon: "person.2.fill",
            color: .teal,
            title: "Social Recycler",
            description: "Refer 3 friends to Mobius",
            pointsReward: 250,
            isUnlocked: false
        ),
    ]
}

#Preview {
    NavigationStack {
        AchievementsView()
    }
}
