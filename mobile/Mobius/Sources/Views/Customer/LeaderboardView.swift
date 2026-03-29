import SwiftUI

struct LeaderboardView: View {
    @Environment(AuthManager.self) private var auth
    @Environment(CustomerService.self) private var customerService

    var body: some View {
        List {
            if customerService.isLoadingLeaderboard && customerService.leaderboard.isEmpty {
                Section {
                    HStack {
                        Spacer()
                        ProgressView("Loading leaderboard...")
                        Spacer()
                    }
                }
            } else if customerService.leaderboard.isEmpty {
                Section {
                    ContentUnavailableView(
                        "No Rankings Yet",
                        systemImage: "trophy.fill",
                        description: Text("Start recycling to appear on the leaderboard.")
                    )
                    .listRowBackground(Color.clear)
                }
            } else {
                // Podium top 3
                let top3 = customerService.leaderboard.prefix(3)
                if !top3.isEmpty {
                    Section("Top Recyclers") {
                        ForEach(Array(top3)) { entry in
                            podiumRow(entry)
                        }
                    }
                }

                // Current user highlight
                if let user = auth.currentUser {
                    Section("Your Ranking") {
                        yourRankRow(user)
                    }
                }

                // Remaining leaderboard
                let remaining = customerService.leaderboard.dropFirst(3)
                if !remaining.isEmpty {
                    Section("More Rankings") {
                        ForEach(Array(remaining)) { entry in
                            leaderboardRow(entry)
                        }
                    }
                }
            }
        }
        .refreshable {
            await customerService.fetchLeaderboard()
        }
        .navigationTitle("Leaderboard")
        .task {
            if customerService.leaderboard.isEmpty {
                await customerService.fetchLeaderboard()
            }
        }
    }

    // MARK: - Your Rank

    private func yourRankRow(_ user: User) -> some View {
        HStack(spacing: 14) {
            // Check if user appears in leaderboard
            let userRank = customerService.leaderboard.first { $0.name == user.name }?.rank

            Text("\(userRank ?? 0)")
                .font(.headline.bold())
                .foregroundStyle(.white)
                .frame(width: 32, height: 32)
                .background(.green, in: RoundedRectangle(cornerRadius: 7))

            VStack(alignment: .leading, spacing: 2) {
                Text("\(user.name) (You)")
                    .font(.body.bold())
                Text("\(user.currentStreak)-day streak")
                    .font(.caption)
                    .foregroundStyle(.secondary)
            }

            Spacer()

            Text("\(customerService.stats?.pointsBalance ?? user.pointsBalance) pts")
                .font(.subheadline.bold())
                .foregroundStyle(.green)
        }
        .listRowBackground(Color.green.opacity(0.08))
    }

    // MARK: - Podium Row (top 3 with medals)

    private func podiumRow(_ entry: LeaderboardEntry) -> some View {
        HStack(spacing: 14) {
            Image(systemName: "medal.fill")
                .font(.system(size: 16, weight: .medium))
                .foregroundStyle(.white)
                .frame(width: 32, height: 32)
                .background(medalColor(for: entry.rank), in: RoundedRectangle(cornerRadius: 7))

            VStack(alignment: .leading, spacing: 2) {
                Text(entry.name)
                    .font(.body)
                Text("\(entry.currentStreak)-day streak")
                    .font(.caption)
                    .foregroundStyle(.secondary)
            }

            Spacer()

            Text("\(entry.points) pts")
                .font(.subheadline.bold())
                .foregroundStyle(.secondary)
        }
    }

    // MARK: - Regular Row

    private func leaderboardRow(_ entry: LeaderboardEntry) -> some View {
        HStack(spacing: 14) {
            Text("\(entry.rank)")
                .font(.subheadline.bold())
                .foregroundStyle(.white)
                .frame(width: 32, height: 32)
                .background(.gray, in: RoundedRectangle(cornerRadius: 7))

            VStack(alignment: .leading, spacing: 2) {
                Text(entry.name)
                    .font(.body)
                Text("\(entry.currentStreak)-day streak")
                    .font(.caption)
                    .foregroundStyle(.secondary)
            }

            Spacer()

            Text("\(entry.points) pts")
                .font(.subheadline.bold())
                .foregroundStyle(.secondary)
        }
    }

    // MARK: - Medal Color

    private func medalColor(for rank: Int) -> Color {
        switch rank {
        case 1: .yellow
        case 2: Color(red: 0.75, green: 0.75, blue: 0.78)
        case 3: Color(red: 0.80, green: 0.50, blue: 0.20)
        default: .gray
        }
    }
}

#Preview {
    NavigationStack {
        LeaderboardView()
    }
    .environment(AuthManager.mockAuthenticated())
    .environment(CustomerService.mockWithData())
}
