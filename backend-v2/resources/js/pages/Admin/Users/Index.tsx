import { Head, Link, usePage } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { formatPhoneForDisplay } from '@/lib/phone';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

type User = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    roles: string[];
    organization: string | null;
    points_balance: number;
    created_at: string;
};

type PaginatedUsers = {
    data: User[];
    total: number;
    last_page: number;
    links: { url: string | null; label: string; active: boolean }[];
};

export default function UsersIndex({ users }: { users: PaginatedUsers }) {
    const { flash } = usePage<{ flash: { generated_password?: string } }>()
        .props;

    return (
        <>
            <Head title="Users" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Users</h1>
                    <Link
                        href="/admin/users/create"
                        className="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                    >
                        Create User
                    </Link>
                </div>

                {flash?.generated_password && (
                    <div className="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950">
                        <p className="text-sm font-medium text-amber-700 dark:text-amber-400">
                            User created. Generated password:{' '}
                            <span className="font-mono">
                                {flash.generated_password}
                            </span>
                        </p>
                    </div>
                )}

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="w-12">ID</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Phone</TableHead>
                                <TableHead>Organization</TableHead>
                                <TableHead>Roles</TableHead>
                                <TableHead className="text-right">
                                    Points
                                </TableHead>
                                <TableHead>Joined</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {users.data.map((user) => (
                                <TableRow key={user.id}>
                                    <TableCell className="font-mono text-sm">
                                        {user.id}
                                    </TableCell>
                                    <TableCell className="font-medium">
                                        {user.name}
                                    </TableCell>
                                    <TableCell>{user.email}</TableCell>
                                    <TableCell>
                                        {formatPhoneForDisplay(user.phone)}
                                    </TableCell>
                                    <TableCell>
                                        {user.organization ?? '-'}
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex gap-1">
                                            {user.roles.map((role) => (
                                                <Badge
                                                    key={role}
                                                    variant="secondary"
                                                >
                                                    {role.replace(/_/g, ' ')}
                                                </Badge>
                                            ))}
                                        </div>
                                    </TableCell>
                                    <TableCell className="text-right font-mono">
                                        {user.points_balance}
                                    </TableCell>
                                    <TableCell>{user.created_at}</TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>

                {users.last_page > 1 && (
                    <div className="flex items-center justify-center gap-2">
                        {users.links.map((link, i) => (
                            <Link
                                key={i}
                                href={link.url ?? '#'}
                                className={`rounded-md border px-3 py-1 text-sm ${
                                    link.active
                                        ? 'bg-primary text-primary-foreground'
                                        : ''
                                } ${!link.url ? 'pointer-events-none opacity-50' : ''}`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                                preserveState
                            />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

UsersIndex.layout = {
    breadcrumbs: [
        { title: 'Admin Dashboard', href: '/admin' },
        { title: 'Users', href: '/admin/users' },
    ],
};
