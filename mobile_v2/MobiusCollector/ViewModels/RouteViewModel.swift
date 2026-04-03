import Foundation

@MainActor
@Observable
final class RouteViewModel {
    var routes: [CollectionRoute] = []
    var selectedRoute: CollectionRoute?
    var isLoading = false
    var isGenerating = false
    var errorMessage: String?

    func fetchRoutes() async {
        isLoading = true
        errorMessage = nil

        do {
            let response: RoutesResponse = try await APIService.shared.request(
                method: "GET",
                path: "/collector/routes"
            )
            routes = response.routes
        } catch {
            errorMessage = error.localizedDescription
        }

        isLoading = false
    }

    func fetchRouteDetail(id: Int) async {
        isLoading = true
        selectedRoute = nil
        errorMessage = nil

        do {
            let response: RouteDetailResponse = try await APIService.shared.request(
                method: "GET",
                path: "/collector/routes/\(id)"
            )
            selectedRoute = response.route
        } catch {
            errorMessage = error.localizedDescription
        }

        isLoading = false
    }

    func generateRoute() async {
        isGenerating = true
        errorMessage = nil

        do {
            try await APIService.shared.post(path: "/collector/routes/generate")
            await fetchRoutes()
        } catch {
            errorMessage = error.localizedDescription
        }

        isGenerating = false
    }

    func acceptRoute(id: Int) async {
        do {
            try await APIService.shared.post(path: "/collector/routes/\(id)/accept")
            await fetchRouteDetail(id: id)
            await fetchRoutes()
        } catch {
            errorMessage = error.localizedDescription
        }
    }

    func rejectRoute(id: Int) async {
        do {
            try await APIService.shared.post(path: "/collector/routes/\(id)/reject")
            selectedRoute = nil
            await fetchRoutes()
        } catch {
            errorMessage = error.localizedDescription
        }
    }

    func startRoute(id: Int) async {
        do {
            try await APIService.shared.post(path: "/collector/routes/\(id)/start")
            await fetchRouteDetail(id: id)
        } catch {
            errorMessage = error.localizedDescription
        }
    }

    func completeStop(routeId: Int, stopOrder: Int, latitude: Double, longitude: Double) async {
        do {
            try await APIService.shared.post(
                path: "/collector/routes/\(routeId)/stops/\(stopOrder)/complete",
                body: ["latitude": latitude, "longitude": longitude]
            )
            await fetchRouteDetail(id: routeId)
        } catch {
            errorMessage = error.localizedDescription
        }
    }

    func skipStop(routeId: Int, stopOrder: Int, reason: String) async {
        do {
            try await APIService.shared.post(
                path: "/collector/routes/\(routeId)/stops/\(stopOrder)/skip",
                body: ["reason": reason]
            )
            await fetchRouteDetail(id: routeId)
        } catch {
            errorMessage = error.localizedDescription
        }
    }

    func completeRoute(id: Int) async {
        do {
            try await APIService.shared.post(path: "/collector/routes/\(id)/complete")
            await fetchRouteDetail(id: id)
            await fetchRoutes()
        } catch {
            errorMessage = error.localizedDescription
        }
    }
}
