import Foundation

/// Service for managing collector routes via the Laravel backend.
@MainActor @Observable
final class RouteService {
    private let api = APIClient()

    /// Set to false to use real backend API
    var useHardcodedRoutes = false

    var routes: [CollectionRoute] = []
    var activeRoute: CollectionRoute?
    var isLoading = false
    var error: String?

    // MARK: - Fetch Routes

    func fetchRoutes() async {
        isLoading = true
        error = nil

        do {
            let response: RoutesListResponse = try await api.get("/collector/routes")
            routes = response.routes
            activeRoute = routes.first { $0.status == .inProgress || $0.status == .accepted }
        } catch {
            self.error = error.localizedDescription
        }
        isLoading = false
    }

    func fetchRouteDetail(_ routeId: Int) async -> CollectionRoute? {
        do {
            let response: RouteDetailResponse = try await api.get("/collector/routes/\(routeId)")
            // Update local cache
            if let idx = routes.firstIndex(where: { $0.id == routeId }) {
                routes[idx] = response.route
            }
            if response.route.status == .inProgress || response.route.status == .accepted {
                activeRoute = response.route
            }
            return response.route
        } catch {
            self.error = error.localizedDescription
            return nil
        }
    }

    // MARK: - Route Lifecycle

    func acceptRoute(_ routeId: Int) async -> Bool {
        do {
            let _: APISimpleMessageResponse = try await api.post("/collector/routes/\(routeId)/accept")
            _ = await fetchRouteDetail(routeId)
            return true
        } catch {
            self.error = error.localizedDescription
            return false
        }
    }

    func startRoute(_ routeId: Int) async -> Bool {
        do {
            let _: APISimpleMessageResponse = try await api.post("/collector/routes/\(routeId)/start")
            _ = await fetchRouteDetail(routeId)
            return true
        } catch {
            self.error = error.localizedDescription
            return false
        }
    }

    func completeStop(routeId: Int, order: Int, latitude: Double, longitude: Double) async -> Bool {
        do {
            let body = CompleteStopRequest(latitude: latitude, longitude: longitude)
            let _: APISimpleMessageResponse = try await api.post("/collector/routes/\(routeId)/stops/\(order)/complete", body: body)
            _ = await fetchRouteDetail(routeId)
            return true
        } catch {
            self.error = error.localizedDescription
            return false
        }
    }

    func skipStop(routeId: Int, order: Int, reason: String) async -> Bool {
        do {
            let body = SkipStopRequest(reason: reason)
            let _: APISimpleMessageResponse = try await api.post("/collector/routes/\(routeId)/stops/\(order)/skip", body: body)
            _ = await fetchRouteDetail(routeId)
            return true
        } catch {
            self.error = error.localizedDescription
            return false
        }
    }

    func completeRoute(_ routeId: Int) async -> Bool {
        do {
            let _: APISimpleMessageResponse = try await api.post("/collector/routes/\(routeId)/complete")
            _ = await fetchRouteDetail(routeId)
            return true
        } catch {
            self.error = error.localizedDescription
            return false
        }
    }

    func rejectRoute(_ routeId: Int) async -> Bool {
        do {
            let _: APISimpleMessageResponse = try await api.post("/collector/routes/\(routeId)/reject")
            routes.removeAll { $0.id == routeId }
            if activeRoute?.id == routeId { activeRoute = nil }
            return true
        } catch {
            self.error = error.localizedDescription
            return false
        }
    }

    func generateRoutes() async -> Bool {
        isLoading = true
        do {
            let _: APISimpleMessageResponse = try await api.post("/collector/routes/generate")
            await fetchRoutes()
            return true
        } catch {
            self.error = error.localizedDescription
            isLoading = false
            return false
        }
    }
}

// MARK: - API Response Types

private struct RoutesListResponse: Decodable {
    let routes: [CollectionRoute]
}

private struct RouteDetailResponse: Decodable {
    let route: CollectionRoute
}

private struct SkipStopRequest: Encodable, Sendable {
    let reason: String
}

private struct CompleteStopRequest: Encodable, Sendable {
    let latitude: Double
    let longitude: Double
}

struct APISimpleMessageResponse: Decodable {
    let message: String
}
