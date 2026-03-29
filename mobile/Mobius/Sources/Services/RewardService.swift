import Foundation

/// Manages reward catalog, redemption, and redemption history.
@MainActor
@Observable
final class RewardService {
    private(set) var rewards: [Reward] = []
    private(set) var redemptions: [Redemption] = []
    private(set) var isLoadingRewards = false
    private(set) var isLoadingRedemptions = false
    private(set) var hasMoreRedemptions = true
    private var redemptionPage = 1

    var error: String?

    private let api = APIClient()

    // MARK: - Fetch Rewards

    func fetchRewards() async {
        isLoadingRewards = true
        defer { isLoadingRewards = false }

        do {
            let response: APIDataResponse<[Reward]> = try await api.get("/customer/rewards")
            rewards = response.data
        } catch {
            self.error = error.localizedDescription
        }
    }

    // MARK: - Redeem Reward

    func redeemReward(id: Int) async -> RedeemResult? {
        do {
            let response: APIMessageResponse<RedeemResult> = try await api.post("/customer/rewards/\(id)/redeem")
            // Refresh rewards to update stock
            await fetchRewards()
            return response.data
        } catch is APIError {
            self.error = "Not enough points or reward unavailable."
            return nil
        } catch {
            self.error = error.localizedDescription
            return nil
        }
    }

    // MARK: - Redemption History (paginated)

    func fetchRedemptions(refresh: Bool = false) async {
        if refresh {
            redemptionPage = 1
            hasMoreRedemptions = true
        }
        guard hasMoreRedemptions, !isLoadingRedemptions else { return }
        isLoadingRedemptions = true
        defer { isLoadingRedemptions = false }

        do {
            let response: PaginatedResponse<Redemption> = try await api.get("/customer/redemptions?page=\(redemptionPage)")
            if refresh {
                redemptions = response.data
            } else {
                redemptions.append(contentsOf: response.data)
            }
            hasMoreRedemptions = response.currentPage < response.lastPage
            redemptionPage = response.currentPage + 1
        } catch {
            self.error = error.localizedDescription
        }
    }

    // MARK: - Mock

    nonisolated static func mockWithData() -> RewardService {
        MainActor.assumeIsolated {
            let service = RewardService()
            service.rewards = [
                Reward(
                    id: 1, name: "Free Americano", description: "Hot or iced",
                    pointsCost: 100, stock: 60, imagePath: nil, expiresAt: nil,
                    brand: RewardBrand(id: 1, name: "ZUS Coffee", slug: "zus-coffee", color: "#1A1A2E")
                ),
                Reward(
                    id: 2, name: "RM5 Off Any Drink", description: nil,
                    pointsCost: 200, stock: 30, imagePath: nil, expiresAt: nil,
                    brand: RewardBrand(id: 2, name: "Starbucks", slug: "starbucks", color: "#00704A")
                ),
            ]
            return service
        }
    }

    nonisolated static func mockEmpty() -> RewardService {
        MainActor.assumeIsolated {
            RewardService()
        }
    }
}
