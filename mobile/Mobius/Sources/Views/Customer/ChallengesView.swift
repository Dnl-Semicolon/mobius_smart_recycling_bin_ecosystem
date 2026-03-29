import SwiftUI

struct ChallengesView: View {
    private let challenges = Challenge.mockData

    var body: some View {
        List {
            Section("Active Challenges") {
                ForEach(challenges.filter { !$0.isCompleted }) { challenge in
                    challengeRow(challenge)
                }
            }

            Section("Completed") {
                ForEach(challenges.filter(\.isCompleted)) { challenge in
                    challengeRow(challenge)
                }
            }

            // Info footer
            Section {
                HStack(spacing: 8) {
                    Image(systemName: "info.circle.fill")
                        .foregroundStyle(.secondary)
                    Text("Complete challenges to earn bonus points and unlock special rewards!")
                        .font(.caption)
                        .foregroundStyle(.secondary)
                }
                .listRowBackground(Color.clear)
            }
        }
        .navigationTitle("Challenges")
        .refreshable {
            try? await Task.sleep(for: .milliseconds(500))
        }
    }

    // MARK: - Challenge Row

    private func challengeRow(_ challenge: Challenge) -> some View {
        VStack(alignment: .leading, spacing: 10) {
            HStack {
                Image(systemName: challenge.icon)
                    .font(.system(size: 16, weight: .medium))
                    .foregroundStyle(.white)
                    .frame(width: 32, height: 32)
                    .background(challenge.typeColor, in: RoundedRectangle(cornerRadius: 7))

                VStack(alignment: .leading, spacing: 2) {
                    Text(challenge.title)
                        .font(.body.bold())
                    Text(challenge.description)
                        .font(.caption)
                        .foregroundStyle(.secondary)
                }

                Spacer()

                Text(challenge.typeLabel)
                    .font(.caption2.bold())
                    .padding(.horizontal, 8)
                    .padding(.vertical, 4)
                    .background(challenge.typeColor.opacity(0.15), in: Capsule())
                    .foregroundStyle(challenge.typeColor)
            }

            // Progress bar
            VStack(alignment: .leading, spacing: 4) {
                ProgressView(value: Double(challenge.current), total: Double(challenge.target))
                    .tint(challenge.isCompleted ? .green : challenge.typeColor)

                HStack {
                    Text("\(challenge.current)/\(challenge.target)")
                        .font(.caption2)
                        .foregroundStyle(.secondary)

                    Spacer()

                    Text("\(challenge.pointsReward) pts")
                        .font(.caption2.bold())
                        .foregroundStyle(.orange)

                    if !challenge.isCompleted {
                        Text("·")
                            .foregroundStyle(.secondary)
                        Text(challenge.timeRemaining)
                            .font(.caption2)
                            .foregroundStyle(.secondary)
                    }
                }
            }

            if challenge.isCompleted {
                HStack(spacing: 6) {
                    Image(systemName: "checkmark.circle.fill")
                        .font(.caption)
                        .foregroundStyle(.green)
                    Text("Challenge Completed!")
                        .font(.caption.bold())
                        .foregroundStyle(.green)
                }
                .padding(8)
                .frame(maxWidth: .infinity, alignment: .leading)
                .background(.green.opacity(0.08), in: RoundedRectangle(cornerRadius: 6))
            }
        }
        .padding(.vertical, 4)
    }
}

// MARK: - Challenge Model

private struct Challenge: Identifiable {
    let id: String
    let icon: String
    let title: String
    let description: String
    let type: ChallengeType
    let current: Int
    let target: Int
    let pointsReward: Int
    let timeRemaining: String

    var isCompleted: Bool { current >= target }

    var typeLabel: String { type.rawValue }

    var typeColor: Color {
        switch type {
        case .daily: .blue
        case .weekly: .purple
        case .monthly: .orange
        }
    }

    enum ChallengeType: String {
        case daily = "Daily"
        case weekly = "Weekly"
        case monthly = "Monthly"
    }

    static let mockData: [Challenge] = [
        Challenge(
            id: "daily_recycle",
            icon: "cup.and.saucer.fill",
            title: "Daily Recycler",
            description: "Recycle 3 items today",
            type: .daily,
            current: 1,
            target: 3,
            pointsReward: 30,
            timeRemaining: "8h left"
        ),
        Challenge(
            id: "weekly_streak",
            icon: "flame.fill",
            title: "Streak Keeper",
            description: "Recycle every day this week",
            type: .weekly,
            current: 5,
            target: 7,
            pointsReward: 100,
            timeRemaining: "2d left"
        ),
        Challenge(
            id: "monthly_cups",
            icon: "star.fill",
            title: "Cup Collector",
            description: "Recycle 50 cups this month",
            type: .monthly,
            current: 32,
            target: 50,
            pointsReward: 250,
            timeRemaining: "12d left"
        ),
        Challenge(
            id: "weekly_types",
            icon: "square.grid.2x2.fill",
            title: "Variety Pack",
            description: "Recycle 4 different waste types this week",
            type: .weekly,
            current: 4,
            target: 4,
            pointsReward: 75,
            timeRemaining: ""
        ),
        Challenge(
            id: "daily_early",
            icon: "sunrise.fill",
            title: "Early Bird",
            description: "Recycle before 9 AM",
            type: .daily,
            current: 1,
            target: 1,
            pointsReward: 20,
            timeRemaining: ""
        ),
    ]
}

#Preview {
    NavigationStack {
        ChallengesView()
    }
}
