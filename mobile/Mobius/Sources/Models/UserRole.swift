import SwiftUI

enum UserRole: String, Codable, CaseIterable, Identifiable {
    case publicUser = "public_user"
    case collector = "collector"
    case storeOwner = "store_owner"
    case admin = "admin"

    var id: String { rawValue }

    var label: String {
        switch self {
        case .publicUser: "Customer"
        case .collector: "Collector"
        case .storeOwner: "Store Owner"
        case .admin: "Admin"
        }
    }

    var icon: String {
        switch self {
        case .publicUser: "person.fill"
        case .collector: "shippingbox.fill"
        case .storeOwner: "storefront.fill"
        case .admin: "gearshape.2.fill"
        }
    }

    var iconColor: Color {
        switch self {
        case .publicUser: .green
        case .collector: .orange
        case .storeOwner: .blue
        case .admin: .purple
        }
    }

    var description: String {
        switch self {
        case .publicUser: "Scan items and earn rewards"
        case .collector: "Pickup routes and tasks"
        case .storeOwner: "Manage outlets and analytics"
        case .admin: "System administration"
        }
    }

    /// Roles that should be available on mobile
    static var mobileRoles: [UserRole] {
        [.publicUser, .collector, .storeOwner]
    }
}
