import SwiftUI

struct RewardsView: View {
    @Environment(AuthManager.self) private var auth
    @Environment(RewardService.self) private var rewardService

    @State private var selectedReward: Reward?
    @State private var redeemResult: RedeemResult?
    @State private var showRedeemConfirm = false
    @State private var showVoucherAlert = false
    @State private var isRedeeming = false

    var body: some View {
        List {
            // Points balance header
            Section {
                HStack {
                    VStack(alignment: .leading, spacing: 4) {
                        Text("Available Points")
                            .font(.subheadline)
                            .foregroundStyle(.secondary)
                        Text("\(auth.currentUser?.pointsBalance ?? 0)")
                            .font(.system(size: 32, weight: .bold, design: .rounded))
                    }
                    Spacer()
                    Image(systemName: "star.circle.fill")
                        .font(.system(size: 40))
                        .foregroundStyle(.yellow)
                }
                .listRowBackground(Color.clear)
            }

            // Available rewards
            rewardsSection

            // Redemption history
            redemptionSection
        }
        .navigationTitle("Rewards")
        .refreshable {
            async let r: () = rewardService.fetchRewards()
            async let d: () = rewardService.fetchRedemptions(refresh: true)
            _ = await (r, d)
        }
        .task {
            if rewardService.rewards.isEmpty {
                await rewardService.fetchRewards()
            }
            if rewardService.redemptions.isEmpty {
                await rewardService.fetchRedemptions(refresh: true)
            }
        }
        .alert("Redeem Reward", isPresented: $showRedeemConfirm, presenting: selectedReward) { reward in
            Button("Cancel", role: .cancel) {}
            Button("Redeem for \(reward.pointsCost) pts") {
                Task { await redeem(reward) }
            }
        } message: { reward in
            Text("Redeem \(reward.name) for \(reward.pointsCost) points?")
        }
        .alert("Reward Redeemed!", isPresented: $showVoucherAlert, presenting: redeemResult) { _ in
            Button("OK") {
                redeemResult = nil
            }
        } message: { result in
            Text("Your voucher code:\n\(result.voucherCode)\n\nPoints spent: \(result.pointsSpent)\nRemaining: \(result.newBalance)")
        }
    }

    // MARK: - Available Rewards

    @ViewBuilder
    private var rewardsSection: some View {
        Section("Available Rewards") {
            if rewardService.isLoadingRewards && rewardService.rewards.isEmpty {
                HStack {
                    Spacer()
                    ProgressView("Loading rewards...")
                    Spacer()
                }
            } else if rewardService.rewards.isEmpty {
                HStack {
                    Image(systemName: "gift.fill")
                        .foregroundStyle(.secondary)
                    Text("No rewards available yet")
                        .foregroundStyle(.secondary)
                }
            } else {
                ForEach(rewardService.rewards) { reward in
                    rewardRow(reward)
                }
            }
        }
    }

    // MARK: - Redemption History

    @ViewBuilder
    private var redemptionSection: some View {
        Section("Recently Redeemed") {
            if rewardService.isLoadingRedemptions && rewardService.redemptions.isEmpty {
                HStack {
                    Spacer()
                    ProgressView()
                    Spacer()
                }
            } else if rewardService.redemptions.isEmpty {
                HStack {
                    Image(systemName: "clock.fill")
                        .foregroundStyle(.secondary)
                    Text("No redemptions yet")
                        .foregroundStyle(.secondary)
                }
            } else {
                ForEach(rewardService.redemptions) { redemption in
                    redemptionRow(redemption)
                }

                if rewardService.hasMoreRedemptions {
                    HStack {
                        Spacer()
                        ProgressView()
                            .task {
                                await rewardService.fetchRedemptions()
                            }
                        Spacer()
                    }
                }
            }
        }
    }

    // MARK: - Reward Row

    private func rewardRow(_ reward: Reward) -> some View {
        Button {
            selectedReward = reward
            showRedeemConfirm = true
        } label: {
            HStack(spacing: 14) {
                Image(systemName: "gift.fill")
                    .font(.system(size: 16, weight: .medium))
                    .foregroundStyle(.white)
                    .frame(width: 32, height: 32)
                    .background(brandColor(reward.brand?.color), in: RoundedRectangle(cornerRadius: 7))

                VStack(alignment: .leading, spacing: 2) {
                    Text(reward.name)
                        .font(.body)
                        .foregroundStyle(.primary)
                    if let brandName = reward.brand?.name {
                        Text(brandName)
                            .font(.caption)
                            .foregroundStyle(.secondary)
                    }
                    if let desc = reward.description {
                        Text(desc)
                            .font(.caption2)
                            .foregroundStyle(.tertiary)
                            .lineLimit(1)
                    }
                }

                Spacer()

                VStack(alignment: .trailing, spacing: 2) {
                    Text("\(reward.pointsCost) pts")
                        .font(.subheadline.bold())
                        .foregroundStyle((auth.currentUser?.pointsBalance ?? 0) >= reward.pointsCost ? .orange : .secondary)
                    if (auth.currentUser?.pointsBalance ?? 0) < reward.pointsCost {
                        Text("Not enough pts")
                            .font(.caption2)
                            .foregroundStyle(.secondary)
                    } else if reward.stock <= 10 {
                        Text("\(reward.stock) left")
                            .font(.caption2)
                            .foregroundStyle(.red)
                    }
                }
            }
        }
        .disabled(isRedeeming || (auth.currentUser?.pointsBalance ?? 0) < reward.pointsCost)
    }

    // MARK: - Redemption Row

    private func redemptionRow(_ redemption: Redemption) -> some View {
        HStack(spacing: 14) {
            Image(systemName: statusIcon(redemption.status))
                .font(.system(size: 16, weight: .medium))
                .foregroundStyle(.white)
                .frame(width: 32, height: 32)
                .background(statusColor(redemption.status), in: RoundedRectangle(cornerRadius: 7))

            VStack(alignment: .leading, spacing: 2) {
                Text(redemption.reward?.name ?? "Reward")
                    .font(.body)
                if let brandName = redemption.reward?.brand?.name {
                    Text(brandName)
                        .font(.caption)
                        .foregroundStyle(.secondary)
                }
            }

            Spacer()

            VStack(alignment: .trailing, spacing: 2) {
                Text("-\(redemption.pointsSpent) pts")
                    .font(.subheadline.bold())
                    .foregroundStyle(.red)
                Text(redemption.status.capitalized)
                    .font(.caption2)
                    .foregroundStyle(.secondary)
            }
        }
    }

    // MARK: - Redeem Action

    private func redeem(_ reward: Reward) async {
        isRedeeming = true
        defer { isRedeeming = false }

        if let result = await rewardService.redeemReward(id: reward.id) {
            redeemResult = result
            showVoucherAlert = true
            // Refresh user to update points balance
            await auth.fetchCurrentUser()
            await rewardService.fetchRedemptions(refresh: true)
        }
    }

    // MARK: - Helpers

    private func brandColor(_ hex: String?) -> Color {
        guard let hex, !hex.isEmpty else { return .purple }
        let cleaned = hex.trimmingCharacters(in: CharacterSet(charactersIn: "#"))
        guard cleaned.count == 6, let rgb = UInt64(cleaned, radix: 16) else { return .purple }
        return Color(
            red: Double((rgb >> 16) & 0xFF) / 255,
            green: Double((rgb >> 8) & 0xFF) / 255,
            blue: Double(rgb & 0xFF) / 255
        )
    }

    private func statusIcon(_ status: String) -> String {
        switch status {
        case "fulfilled": "checkmark.circle.fill"
        case "expired": "clock.badge.xmark"
        default: "hourglass"
        }
    }

    private func statusColor(_ status: String) -> Color {
        switch status {
        case "fulfilled": .green
        case "expired": .gray
        default: .orange
        }
    }
}

#Preview {
    NavigationStack {
        RewardsView()
    }
    .environment(AuthManager.mockAuthenticated())
    .environment(RewardService.mockWithData())
}
