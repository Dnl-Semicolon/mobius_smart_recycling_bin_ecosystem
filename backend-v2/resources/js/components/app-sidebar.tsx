import { Link, usePage } from '@inertiajs/react';
import {
    Building2,
    ClipboardList,
    CreditCard,
    LayoutGrid,
    MapPin,
    Package,
    Route,
    ScanLine,
    ScrollText,
    Send,
    ShieldCheck,
    Store,
    Trash2,
    Truck,
    Users,
    History,
    Ticket,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

function getNavItemsForRole(roles: string[]): NavItem[] {
    const role = roles[0] ?? 'public_user';

    switch (role) {
        case 'admin':
            return [
                { title: 'Dashboard', href: '/admin', icon: LayoutGrid },
                { title: 'Organizations', href: '/admin/organizations', icon: Building2 },
                { title: 'Users', href: '/admin/users', icon: Users },
                { title: 'Bins', href: '/admin/bins', icon: Trash2 },
                { title: 'Outlets', href: '/admin/outlets', icon: Store },
                { title: 'Detection Events', href: '/admin/detection-events', icon: ScanLine },
                { title: 'Collection Routes', href: '/admin/routes', icon: Route },
                { title: 'Pickup Requests', href: '/admin/pickup-requests', icon: Truck },
                { title: 'Plans', href: '/admin/plans', icon: ScrollText },
                { title: 'Subscriptions', href: '/admin/subscriptions', icon: ShieldCheck },
                { title: 'Payments', href: '/admin/payments', icon: CreditCard },
            ];
        case 'brand_owner':
            return [
                { title: 'Dashboard', href: '/brand', icon: LayoutGrid },
                { title: 'Outlets', href: '/brand/outlets', icon: Store },
                { title: 'Invitations', href: '/brand/invitations', icon: Send },
            ];
        case 'store_owner':
            return [
                { title: 'Dashboard', href: '/store', icon: LayoutGrid },
                { title: 'My Outlets', href: '/store/outlets', icon: Store },
                { title: 'My Bins', href: '/store/bins', icon: Trash2 },
            ];
        case 'collector':
            return [
                { title: 'Dashboard', href: '/collector', icon: LayoutGrid },
                { title: 'Pickup Requests', href: '/collector/pickup-requests', icon: ClipboardList },
                { title: 'Routes', href: '/collector/routes', icon: Route },
            ];
        case 'agency_admin':
            return [
                { title: 'Dashboard', href: '/agency', icon: LayoutGrid },
                { title: 'Collectors', href: '/agency/collectors', icon: Users },
                { title: 'Routes', href: '/agency/routes', icon: Route },
            ];
        case 'public_user':
        default:
            return [
                { title: 'Dashboard', href: '/public', icon: LayoutGrid },
                { title: 'My Recycling History', href: '/public/history', icon: History },
            ];
    }
}

function getDashboardHref(roles: string[]): string {
    const role = roles[0] ?? 'public_user';
    const prefixMap: Record<string, string> = {
        admin: '/admin',
        brand_owner: '/brand',
        store_owner: '/store',
        collector: '/collector',
        agency_admin: '/agency',
        public_user: '/public',
    };
    return prefixMap[role] ?? '/public';
}

export function AppSidebar() {
    const { auth } = usePage<{ auth: { user: { roles: string[] } } }>().props;
    const roles = (auth.user?.roles as string[]) ?? [];
    const navItems = getNavItemsForRole(roles);
    const dashboardHref = getDashboardHref(roles);

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboardHref} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={navItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
