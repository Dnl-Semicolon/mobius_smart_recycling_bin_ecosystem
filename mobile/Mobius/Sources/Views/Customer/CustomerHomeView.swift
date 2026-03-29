import SwiftUI

struct CustomerHomeView: View {
    @Environment(AuthManager.self) private var auth
    @Environment(RoleManager.self) private var roleManager
    @Environment(NotificationManager.self) private var notificationManager
    @Environment(CustomerService.self) private var customerService

    var body: some View {
        List {
            // Points Card
            if let user = auth.currentUser {
                Section {
                    pointsCard(user)
                }
                .listRowInsets(EdgeInsets())
                .listRowBackground(Color.clear)
            }

            // Quick Actions (Settings-app style list)
            Section("Quick Actions") {
                NavigationLink {
                    ScanView()
                } label: {
                    settingsRowContent(icon: "qrcode.viewfinder", iconColor: .green, title: "Scan & Recycle", subtitle: "Scan a bin QR code")
                }

                NavigationLink {
                    RewardsView()
                } label: {
                    settingsRowContent(icon: "gift.fill", iconColor: .purple, title: "Rewards", subtitle: "Redeem your points")
                }

                NavigationLink {
                    LeaderboardView()
                } label: {
                    settingsRowContent(icon: "trophy.fill", iconColor: .yellow, title: "Leaderboard", subtitle: "See your ranking")
                }

                NavigationLink {
                    ImpactView()
                } label: {
                    settingsRowContent(icon: "chart.bar.fill", iconColor: .blue, title: "My Impact", subtitle: "Environmental stats")
                }
            }

            // Recent Activity
            Section {
                if customerService.isLoadingHistory && customerService.history.isEmpty {
                    HStack {
                        Spacer()
                        ProgressView()
                        Spacer()
                    }
                } else if customerService.history.isEmpty {
                    HStack {
                        Image(systemName: "leaf.fill")
                            .foregroundStyle(.secondary)
                        Text("No recycling activity yet")
                            .foregroundStyle(.secondary)
                    }
                } else {
                    ForEach(customerService.history.prefix(5)) { item in
                        activityRow(item)
                    }
                }
            } header: {
                HStack {
                    Text("Recent Activity")
                    Spacer()
                    if !customerService.history.isEmpty {
                        NavigationLink {
                            ActivityHistoryView()
                        } label: {
                            Text("See All")
                                .font(.subheadline)
                                .textCase(nil)
                        }
                    }
                }
            }
        }
        .refreshable {
            await refreshAll()
        }
        .navigationTitle("Mobius")
        .toolbar {
            ToolbarItem(placement: .topBarLeading) {
                RoleHeaderButton()
            }
            ToolbarItem(placement: .topBarTrailing) {
                NavigationLink {
                    NotificationsView()
                } label: {
                    Image(systemName: notificationManager.unreadCount > 0 ? "bell.badge.fill" : "bell.fill")
                        .symbolRenderingMode(.palette)
                        .foregroundStyle(notificationManager.unreadCount > 0 ? .red : .primary, .primary)
                }
            }
        }
        .task {
            await refreshAll()
        }
    }

    private func refreshAll() async {
        async let s: () = customerService.fetchStats()
        async let h: () = customerService.fetchHistory(refresh: true)
        async let n: () = notificationManager.fetchUnreadCount()
        _ = await (s, h, n)
    }

    // MARK: - Points Card

    private func pointsCard(_ user: User) -> some View {
        VStack(spacing: 12) {
            HStack {
                VStack(alignment: .leading, spacing: 4) {
                    Text("Your Points")
                        .font(.subheadline)
                        .foregroundStyle(.white.opacity(0.8))
                    Text("\(customerService.stats?.pointsBalance ?? user.pointsBalance)")
                        .font(.system(size: 36, weight: .bold, design: .rounded))
                        .foregroundStyle(.white)
                }
                Spacer()
                Image(systemName: "leaf.fill")
                    .font(.system(size: 40))
                    .foregroundStyle(.white.opacity(0.3))
            }

            HStack(spacing: 20) {
                statPill(icon: "flame.fill", value: "\(customerService.stats?.currentStreak ?? user.currentStreak)", label: "Streak")
                statPill(icon: "cup.and.saucer.fill", value: "\(customerService.stats?.totalDetections ?? 0)", label: "Recycled")
                statPill(icon: "cloud.fill", value: customerService.stats?.co2SavedFormatted ?? "0g", label: "CO₂ Saved")
            }
        }
        .padding(20)
        .background(
            LinearGradient(
                colors: [.green, .green.opacity(0.8)],
                startPoint: .topLeading,
                endPoint: .bottomTrailing
            )
        )
        .clipShape(RoundedRectangle(cornerRadius: 16))
    }

    private func statPill(icon: String, value: String, label: String) -> some View {
        VStack(spacing: 4) {
            HStack(spacing: 4) {
                Image(systemName: icon)
                    .font(.caption2)
                Text(value)
                    .font(.subheadline.bold())
            }
            Text(label)
                .font(.caption2)
                .opacity(0.8)
        }
        .foregroundStyle(.white)
    }

    // MARK: - Activity Row

    private func activityRow(_ item: RecyclingHistoryItem) -> some View {
        HStack(spacing: 14) {
            if let wasteType = item.wasteType {
                Image(systemName: wasteType.icon)
                    .font(.system(size: 16, weight: .medium))
                    .foregroundStyle(.white)
                    .frame(width: 32, height: 32)
                    .background(wasteType.iconColor, in: RoundedRectangle(cornerRadius: 7))
            } else {
                Image(systemName: "leaf.fill")
                    .font(.system(size: 16, weight: .medium))
                    .foregroundStyle(.white)
                    .frame(width: 32, height: 32)
                    .background(.gray, in: RoundedRectangle(cornerRadius: 7))
            }

            VStack(alignment: .leading, spacing: 2) {
                Text(item.wasteTypeLabel ?? "Unknown")
                    .font(.body)
                Text(item.timeAgo)
                    .font(.caption)
                    .foregroundStyle(.secondary)
            }

            Spacer()

            Text("+\(item.pointsEarned) pts")
                .font(.subheadline.bold())
                .foregroundStyle(.green)
        }
    }

    // MARK: - Shared Row Content

    private func settingsRowContent(icon: String, iconColor: Color, title: String, subtitle: String) -> some View {
        HStack(spacing: 14) {
            Image(systemName: icon)
                .font(.system(size: 16, weight: .medium))
                .foregroundStyle(.white)
                .frame(width: 32, height: 32)
                .background(iconColor, in: RoundedRectangle(cornerRadius: 7))

            VStack(alignment: .leading, spacing: 2) {
                Text(title)
                    .font(.body)
                    .foregroundStyle(.primary)
                Text(subtitle)
                    .font(.caption)
                    .foregroundStyle(.secondary)
            }
        }
    }
}

// MARK: - Time Ago Helper

extension RecyclingHistoryItem {
    var timeAgo: String {
        guard let date = detectedAt else { return "" }
        let interval = Date().timeIntervalSince(date)

        if interval < 3600 {
            let minutes = Int(interval / 60)
            return minutes <= 1 ? "Just now" : "\(minutes) min ago"
        } else if interval < 86400 {
            let hours = Int(interval / 3600)
            return "\(hours) hour\(hours == 1 ? "" : "s") ago"
        } else if interval < 172800 {
            return "Yesterday"
        } else {
            let formatter = DateFormatter()
            formatter.dateFormat = "d MMM"
            return formatter.string(from: date)
        }
    }
}

#Preview {
    NavigationStack {
        CustomerHomeView()
    }
    .environment(AuthManager.mockAuthenticated())
    .environment(RoleManager.mockMultiRole())
    .environment(NotificationManager.mockWithNotifications())
    .environment(CustomerService.mockWithData())
}
