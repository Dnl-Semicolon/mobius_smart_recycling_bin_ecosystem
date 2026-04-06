import { Transition } from '@headlessui/react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/delete-user';
import EmailInput from '@/components/email-input';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import PhoneInput from '@/components/phone-input';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

export default function Profile({
    mustVerifyEmail,
    status,
}: {
    mustVerifyEmail: boolean;
    status?: string;
}) {
    const { auth } = usePage<{ auth: { user: Record<string, unknown> } }>()
        .props;
    const user = auth.user;
    const form = useForm({
        name: (user.name as string) ?? '',
        email: (user.email as string) ?? '',
        phone: (user.phone as string | null) ?? '',
    });

    function submit(event: React.FormEvent<HTMLFormElement>): void {
        event.preventDefault();

        form.patch(ProfileController.update.url(), {
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title="Profile settings" />

            <h1 className="sr-only">Profile settings</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Profile information"
                    description="Update your name and email address"
                />

                <form className="space-y-6" onSubmit={submit}>
                    <div className="grid gap-2">
                        <Label htmlFor="name">Name</Label>

                        <Input
                            id="name"
                            className="mt-1 block w-full"
                            value={form.data.name}
                            onChange={(event) =>
                                form.setData('name', event.target.value)
                            }
                            required
                            autoComplete="name"
                            placeholder="Full name"
                        />

                        <InputError
                            className="mt-2"
                            message={form.errors.name}
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="email">Email address</Label>

                        <EmailInput
                            id="email"
                            className="mt-1 block w-full"
                            value={form.data.email}
                            onChange={(value) => form.setData('email', value)}
                            required
                            autoComplete="username"
                            placeholder="Email address"
                            error={form.errors.email}
                        />

                        <InputError
                            className="mt-2"
                            message={form.errors.email}
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="phone">Phone</Label>

                        <PhoneInput
                            id="phone"
                            className="mt-1 block w-full"
                            value={form.data.phone}
                            onChange={(value) => form.setData('phone', value)}
                            placeholder="Phone number"
                            error={form.errors.phone}
                        />

                        <InputError
                            className="mt-2"
                            message={form.errors.phone}
                        />
                    </div>

                    {mustVerifyEmail && user.email_verified_at === null && (
                        <div>
                            <p className="-mt-4 text-sm text-muted-foreground">
                                Your email address is unverified.{' '}
                                <Link
                                    href={send()}
                                    as="button"
                                    className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                >
                                    Click here to resend the verification email.
                                </Link>
                            </p>

                            {status === 'verification-link-sent' && (
                                <div className="mt-2 text-sm font-medium text-green-600">
                                    A new verification link has been sent to
                                    your email address.
                                </div>
                            )}
                        </div>
                    )}

                    <div className="flex items-center gap-4">
                        <Button
                            disabled={form.processing}
                            data-test="update-profile-button"
                        >
                            Save
                        </Button>

                        <Transition
                            show={form.recentlySuccessful}
                            enter="transition ease-in-out"
                            enterFrom="opacity-0"
                            leave="transition ease-in-out"
                            leaveTo="opacity-0"
                        >
                            <p className="text-sm text-neutral-600">Saved</p>
                        </Transition>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </>
    );
}

Profile.layout = {
    breadcrumbs: [
        {
            title: 'Profile settings',
            href: edit(),
        },
    ],
};
