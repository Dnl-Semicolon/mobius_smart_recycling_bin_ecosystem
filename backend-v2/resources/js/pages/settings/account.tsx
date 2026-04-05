import { Head, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';

export default function Account({
    organizationName,
}: {
    organizationName?: string;
}) {
    const { auth } = usePage<{ auth: { user: Record<string, unknown> } }>().props;
    const user = auth.user;

    return (
        <>
            <Head title="Account" />

            <h1 className="sr-only">Account</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Account information"
                    description="Read-only details about your account"
                />

                <dl className="grid gap-4 text-sm md:grid-cols-2">
                    {organizationName && (
                        <div>
                            <dt className="text-muted-foreground">Organization</dt>
                            <dd className="mt-1 font-medium">{organizationName}</dd>
                        </div>
                    )}
                    <div>
                        <dt className="text-muted-foreground">Role</dt>
                        <dd className="mt-1 font-medium">
                            {((user.roles as string[]) ?? []).map((r: string) => r.replace(/_/g, ' ')).join(', ')}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-muted-foreground">Points Balance</dt>
                        <dd className="mt-1 font-medium">{String(user.points_balance ?? 0)}</dd>
                    </div>
                    <div>
                        <dt className="text-muted-foreground">Current Streak</dt>
                        <dd className="mt-1 font-medium">{String(user.current_streak ?? 0)}</dd>
                    </div>
                    <div>
                        <dt className="text-muted-foreground">Longest Streak</dt>
                        <dd className="mt-1 font-medium">{String(user.longest_streak ?? 0)}</dd>
                    </div>
                    <div>
                        <dt className="text-muted-foreground">Member Since</dt>
                        <dd className="mt-1 font-medium">{String(user.created_at ?? '-')}</dd>
                    </div>
                </dl>
            </div>
        </>
    );
}

Account.layout = {
    breadcrumbs: [
        {
            title: 'Account',
            href: '/settings/account',
        },
    ],
};
