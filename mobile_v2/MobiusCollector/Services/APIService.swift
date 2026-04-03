import Foundation

@MainActor
final class APIService {
    static let shared = APIService()

    // Use Mac's local IP so both Simulator and real iPhone can reach Laravel.
    // Update this if your IP changes (check with: ipconfig getifaddr en0)
    private let baseURL = "http://172.20.10.3:8000/api/v1"

    var token: String? {
        get { UserDefaults.standard.string(forKey: "auth_token") }
        set {
            if let newValue {
                UserDefaults.standard.set(newValue, forKey: "auth_token")
            } else {
                UserDefaults.standard.removeObject(forKey: "auth_token")
            }
        }
    }

    private init() {}

    func request<T: Decodable>(
        method: String,
        path: String,
        body: [String: Any]? = nil
    ) async throws -> T {
        let url = try makeURL(path: path)
        var request = makeRequest(url: url, method: method)

        if let body {
            request.httpBody = try JSONSerialization.data(withJSONObject: body)
        }

        let (data, response) = try await URLSession.shared.data(for: request)
        try validateResponse(data: data, response: response)
        return try JSONDecoder().decode(T.self, from: data)
    }

    func post(path: String, body: [String: Any]? = nil) async throws {
        let url = try makeURL(path: path)
        var request = makeRequest(url: url, method: "POST")

        if let body {
            request.httpBody = try JSONSerialization.data(withJSONObject: body)
        }

        let (data, response) = try await URLSession.shared.data(for: request)
        try validateResponse(data: data, response: response)
    }

    private func makeURL(path: String) throws -> URL {
        guard let url = URL(string: "\(baseURL)\(path)") else {
            throw APIError.invalidURL
        }
        return url
    }

    private func makeRequest(url: URL, method: String) -> URLRequest {
        var request = URLRequest(url: url)
        request.httpMethod = method
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        request.setValue("application/json", forHTTPHeaderField: "Accept")
        if let token {
            request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
        }
        return request
    }

    private func validateResponse(data: Data, response: URLResponse) throws {
        guard let http = response as? HTTPURLResponse else {
            throw APIError.invalidResponse
        }
        if http.statusCode == 401 {
            token = nil
            UserDefaults.standard.removeObject(forKey: "user_data")
            throw APIError.unauthorized
        }
        guard (200...299).contains(http.statusCode) else {
            let message = String(data: data, encoding: .utf8) ?? "Unknown error"
            throw APIError.serverError(statusCode: http.statusCode, message: message)
        }
    }
}

enum APIError: LocalizedError {
    case invalidURL
    case invalidResponse
    case unauthorized
    case serverError(statusCode: Int, message: String)

    var errorDescription: String? {
        switch self {
        case .invalidURL: "Invalid URL"
        case .invalidResponse: "Invalid response"
        case .unauthorized: "Session expired. Please sign in again."
        case .serverError(let code, let msg): "Error \(code): \(msg)"
        }
    }
}
