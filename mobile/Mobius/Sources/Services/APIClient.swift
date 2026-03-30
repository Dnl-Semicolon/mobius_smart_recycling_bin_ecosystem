import Foundation

/// Central API client for communicating with the Laravel backend.
/// All requests go through here for consistent auth headers, error handling, and JSON decoding.
final class APIClient: Sendable {
    // MARK: - Configuration

    /// Base URL for the Laravel backend.
    /// In debug, can be overridden via UserDefaults key "api_base_url".
    static var baseURL: String {
        #if DEBUG
        if let override = UserDefaults.standard.string(forKey: "api_base_url"), !override.isEmpty {
            return override
        }
        return "http://172.20.10.3:8000/api/v1"
        #else
        return "https://api.mobius.my/api/v1"
        #endif
    }

    private let decoder: JSONDecoder = {
        let d = JSONDecoder()
        d.dateDecodingStrategy = .custom { decoder in
            let container = try decoder.singleValueContainer()
            let string = try container.decode(String.self)

            // Try ISO 8601 with fractional seconds first
            let iso = ISO8601DateFormatter()
            iso.formatOptions = [.withInternetDateTime, .withFractionalSeconds]
            if let date = iso.date(from: string) { return date }

            // Fallback without fractional seconds
            iso.formatOptions = [.withInternetDateTime]
            if let date = iso.date(from: string) { return date }

            // Fallback: Laravel's default format "yyyy-MM-dd HH:mm:ss"
            let formatter = DateFormatter()
            formatter.dateFormat = "yyyy-MM-dd HH:mm:ss"
            formatter.locale = Locale(identifier: "en_US_POSIX")
            if let date = formatter.date(from: string) { return date }

            throw DecodingError.dataCorruptedError(in: container, debugDescription: "Cannot decode date: \(string)")
        }
        return d
    }()

    private let encoder: JSONEncoder = {
        let e = JSONEncoder()
        e.keyEncodingStrategy = .convertToSnakeCase
        return e
    }()

    // MARK: - Token Management

    var hasToken: Bool {
        KeychainService.getToken() != nil
    }

    func setToken(_ token: String) {
        KeychainService.saveToken(token)
    }

    func clearToken() {
        KeychainService.deleteToken()
    }

    // MARK: - Request Building

    private func makeRequest(path: String, method: String = "GET", body: (any Encodable)? = nil) throws -> URLRequest {
        guard let url = URL(string: "\(Self.baseURL)\(path)") else {
            throw APIError.invalidURL
        }

        var request = URLRequest(url: url)
        request.httpMethod = method
        request.setValue("application/json", forHTTPHeaderField: "Accept")
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")

        if let token = KeychainService.getToken() {
            request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
        }

        if let body {
            request.httpBody = try encoder.encode(body)
        }

        return request
    }

    // MARK: - Request Execution

    func get<T: Decodable>(_ path: String) async throws -> T {
        let request = try makeRequest(path: path)
        return try await execute(request)
    }

    func post<T: Decodable>(_ path: String, body: (any Encodable & Sendable)? = nil) async throws -> T {
        let request = try makeRequest(path: path, method: "POST", body: body)
        return try await execute(request)
    }

    func put<T: Decodable>(_ path: String, body: (any Encodable & Sendable)? = nil) async throws -> T {
        let request = try makeRequest(path: path, method: "PUT", body: body)
        return try await execute(request)
    }

    func delete(_ path: String) async throws {
        let request = try makeRequest(path: path, method: "DELETE")
        let (_, response) = try await URLSession.shared.data(for: request)
        try validateResponse(response)
    }

    func delete<T: Decodable>(_ path: String) async throws -> T {
        let request = try makeRequest(path: path, method: "DELETE")
        return try await execute(request)
    }

    /// Upload a file via multipart/form-data and decode the response.
    func upload<T: Decodable>(_ path: String, imageData: Data, filename: String = "avatar.jpg", fieldName: String = "avatar") async throws -> T {
        guard let url = URL(string: "\(Self.baseURL)\(path)") else {
            throw APIError.invalidURL
        }

        let boundary = UUID().uuidString
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("application/json", forHTTPHeaderField: "Accept")
        request.setValue("multipart/form-data; boundary=\(boundary)", forHTTPHeaderField: "Content-Type")

        if let token = KeychainService.getToken() {
            request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
        }

        var body = Data()
        body.append("--\(boundary)\r\n".data(using: .utf8)!)
        body.append("Content-Disposition: form-data; name=\"\(fieldName)\"; filename=\"\(filename)\"\r\n".data(using: .utf8)!)
        body.append("Content-Type: image/jpeg\r\n\r\n".data(using: .utf8)!)
        body.append(imageData)
        body.append("\r\n--\(boundary)--\r\n".data(using: .utf8)!)
        request.httpBody = body

        return try await execute(request)
    }

    private func execute<T: Decodable>(_ request: URLRequest) async throws -> T {
        let (data, response) = try await URLSession.shared.data(for: request)
        try validateResponse(response)
        return try decoder.decode(T.self, from: data)
    }

    private func validateResponse(_ response: URLResponse) throws {
        guard let http = response as? HTTPURLResponse else {
            throw APIError.invalidResponse
        }

        switch http.statusCode {
        case 200...299:
            return
        case 401:
            clearToken()
            throw APIError.unauthorized
        case 403:
            throw APIError.forbidden
        case 404:
            throw APIError.notFound
        case 422:
            throw APIError.validationError
        default:
            throw APIError.serverError(http.statusCode)
        }
    }
}

// MARK: - API Errors

enum APIError: LocalizedError, Sendable {
    case invalidURL
    case invalidResponse
    case unauthorized
    case forbidden
    case notFound
    case validationError
    case serverError(Int)

    var errorDescription: String? {
        switch self {
        case .invalidURL: "Invalid URL"
        case .invalidResponse: "Invalid response from server"
        case .unauthorized: "Session expired. Please log in again."
        case .forbidden: "You don't have permission to do that."
        case .notFound: "Resource not found."
        case .validationError: "Please check your input."
        case .serverError(let code): "Server error (\(code))"
        }
    }
}
