import Foundation

struct User: Codable, Sendable {
    let id: Int
    let name: String
    let email: String
    let roles: [String]?
}

struct LoginResponse: Codable, Sendable {
    let token: String
    let user: User
}
