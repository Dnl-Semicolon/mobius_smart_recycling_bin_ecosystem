import Foundation

/// Service for managing collector routes via the Laravel backend.
@MainActor @Observable
final class RouteService {
    private let api = APIClient()

    /// Set to `true` to bypass the backend and use hardcoded mock routes.
    var useHardcodedRoutes = true

    var routes: [CollectionRoute] = []
    var activeRoute: CollectionRoute?
    var isLoading = false
    var error: String?

    // MARK: - Fetch Routes

    func fetchRoutes() async {
        isLoading = true
        error = nil

        if useHardcodedRoutes {
            routes = [CollectionRoute.mockActive, CollectionRoute.mockPending]
            activeRoute = routes.first { $0.status == .active || $0.status == .accepted }
            isLoading = false
            return
        }

        do {
            let response: APIDataResponse<[CollectionRoute]> = try await api.get("/collector/routes")
            routes = response.data
            activeRoute = routes.first { $0.status == .active || $0.status == .accepted }
        } catch {
            self.error = error.localizedDescription
        }
        isLoading = false
    }

    // MARK: - Route Lifecycle

    func acceptRoute(_ routeId: Int) async -> Bool {
        if useHardcodedRoutes {
            return mutateLocalRoute(routeId) { route in
                route.status = .accepted
            }
        }
        do {
            let response: APIMessageResponse<CollectionRoute> = try await api.post("/collector/routes/\(routeId)/accept")
            updateLocalRoute(response.data)
            return true
        } catch {
            self.error = error.localizedDescription
            return false
        }
    }

    func startRoute(_ routeId: Int) async -> Bool {
        if useHardcodedRoutes {
            return mutateLocalRoute(routeId) { route in
                route.status = .active
                route.startedAt = Date()
            }
        }
        do {
            let response: APIMessageResponse<CollectionRoute> = try await api.post("/collector/routes/\(routeId)/start")
            updateLocalRoute(response.data)
            return true
        } catch {
            self.error = error.localizedDescription
            return false
        }
    }

    func completeStop(routeId: Int, order: Int, latitude: Double, longitude: Double) async -> Bool {
        if useHardcodedRoutes {
            return mutateLocalRoute(routeId) { route in
                if let idx = route.stops.firstIndex(where: { $0.order == order }) {
                    route.stops[idx].status = "completed"
                    route.stops[idx].completedAt = Date()
                    route.stopsCompleted = route.stops.filter { $0.status == "completed" }.count
                }
            }
        }
        do {
            let body = CompleteStopRequest(latitude: latitude, longitude: longitude)
            let response: APIMessageResponse<CollectionRoute> = try await api.post("/collector/routes/\(routeId)/stops/\(order)/complete", body: body)
            updateLocalRoute(response.data)
            return true
        } catch {
            self.error = error.localizedDescription
            return false
        }
    }

    func skipStop(routeId: Int, order: Int, reason: String) async -> Bool {
        if useHardcodedRoutes {
            return mutateLocalRoute(routeId) { route in
                if let idx = route.stops.firstIndex(where: { $0.order == order }) {
                    route.stops[idx].status = "skipped"
                    route.stops[idx].skipReason = reason
                }
            }
        }
        do {
            let body = SkipStopRequest(reason: reason)
            let response: APIMessageResponse<CollectionRoute> = try await api.post("/collector/routes/\(routeId)/stops/\(order)/skip", body: body)
            updateLocalRoute(response.data)
            return true
        } catch {
            self.error = error.localizedDescription
            return false
        }
    }

    func completeRoute(_ routeId: Int) async -> Bool {
        if useHardcodedRoutes {
            return mutateLocalRoute(routeId) { route in
                route.status = .completed
                route.completedAt = Date()
            }
        }
        do {
            let response: APIMessageResponse<CollectionRoute> = try await api.post("/collector/routes/\(routeId)/complete")
            updateLocalRoute(response.data)
            return true
        } catch {
            self.error = error.localizedDescription
            return false
        }
    }

    func rejectRoute(_ routeId: Int) async -> Bool {
        if useHardcodedRoutes {
            routes.removeAll { $0.id == routeId }
            if activeRoute?.id == routeId { activeRoute = nil }
            return true
        }
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
        if useHardcodedRoutes {
            await fetchRoutes()
            return true
        }
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

    // MARK: - Helpers

    /// Mutate a local route in-place (for hardcoded mode).
    private func mutateLocalRoute(_ routeId: Int, _ transform: (inout CollectionRoute) -> Void) -> Bool {
        guard let idx = routes.firstIndex(where: { $0.id == routeId }) else { return false }
        transform(&routes[idx])
        let route = routes[idx]
        if route.status == .active || route.status == .accepted {
            activeRoute = route
        } else if activeRoute?.id == routeId {
            activeRoute = nil
        }
        return true
    }

    private func updateLocalRoute(_ route: CollectionRoute) {
        if let idx = routes.firstIndex(where: { $0.id == route.id }) {
            routes[idx] = route
        }
        if route.status == .active || route.status == .accepted {
            activeRoute = route
        } else if activeRoute?.id == route.id {
            activeRoute = nil
        }
    }

    // MARK: - Mock

    static func mockWithActiveRoute() -> RouteService {
        let service = RouteService()
        service.routes = [CollectionRoute.mockActive, CollectionRoute.mockPending]
        service.activeRoute = CollectionRoute.mockActive
        return service
    }

    static func mockEmpty() -> RouteService {
        RouteService()
    }

    static func mockWithPendingRoute() -> RouteService {
        let service = RouteService()
        service.routes = [CollectionRoute.mockPending]
        return service
    }
}

// MARK: - API Response Types

private struct SkipStopRequest: Encodable, Sendable {
    let reason: String
}

private struct CompleteStopRequest: Encodable, Sendable {
    let latitude: Double
    let longitude: Double
}

struct APIDataResponse<T: Decodable>: Decodable {
    let data: T
}

struct APIMessageResponse<T: Decodable>: Decodable {
    let message: String
    let data: T
}

struct APISimpleMessageResponse: Decodable {
    let message: String
}
