import { Link, usePage } from '@inertiajs/react';
import { ClipboardList, CreditCard, LayoutGrid, Map, Store, Ticket, Trash2, Users } from 'lucide-react';
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
                { title: 'Leads', href: '/admin/leads', icon: ClipboardList },
                { title: 'Users', href: '/admin/users', icon: Users },
                { title: 'Outlets', href: '/admin/outlets', icon: Store },
                { title: 'Bins', href: '/admin/bins', icon: Trash2 },
                { title: 'Routes', href: '/admin/routes', icon: Map },
                { title: 'Vouchers', href: '/admin/vouchers', icon: Ticket },
                { title: 'Billing', href: '/admin/billing', icon: CreditCard },
            ];
        case 'brand_owner':
            return [
                { title: 'Dashboard', href: '/brand', icon: LayoutGrid },
                { title: 'Staff', href: '/brand/staff', icon: Users },
                { title: 'Outlets', href: '/brand/outlets', icon: Store },
                { title: 'Vouchers', href: '/brand/vouchers', icon: Ticket },
                { title: 'Billing', href: '/brand/billing', icon: CreditCard },
            ];
        case 'store_owner':
            return [
                { title: 'Dashboard', href: '/store', icon: LayoutGrid },
                { title: 'Redeem Voucher', href: '/store/redeem', icon: Ticket },
            ];
        case 'collector':
            return [
                { title: 'Dashboard', href: '/collector', icon: LayoutGrid },
                { title: 'Routes', href: '/collector/routes', icon: Map },
            ];
        case 'agency_admin':
            return [
                { title: 'Dashboard', href: '/agency', icon: LayoutGrid },
            ];
        case 'public_user':
        default:
            return [
                { title: 'Dashboard', href: '/public', icon: LayoutGrid },
                { title: 'Vouchers', href: '/public/vouchers', icon: Ticket },
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
