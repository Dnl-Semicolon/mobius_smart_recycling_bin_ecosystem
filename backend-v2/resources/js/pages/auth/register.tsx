import { Head, useForm } from '@inertiajs/react';
import EmailInput from '@/components/email-input';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import PhoneInput from '@/components/phone-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

export default function Register() {
    const form = useForm({
        name: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
    });

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();

        form.post(store.url(), {
            onSuccess: () => form.reset('password', 'password_confirmation'),
        });
    }

    return (
        <>
            <Head title="Register" />
            <form onSubmit={submit} className="flex flex-col gap-6">
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="name">Name</Label>
                        <Input
                            id="name"
                            type="text"
                            required
                            autoFocus
                            tabIndex={1}
                            autoComplete="name"
                            value={form.data.name}
                            onChange={(event) =>
                                form.setData('name', event.target.value)
                            }
                            placeholder="Full name"
                        />
                        <InputError
                            message={form.errors.name}
                            className="mt-2"
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="email">Email address</Label>
                        <EmailInput
                            id="email"
                            required
                            tabIndex={2}
                            autoComplete="email"
                            value={form.data.email}
                            onChange={(value) => form.setData('email', value)}
                            placeholder="email@example.com"
                            error={form.errors.email}
                        />
                        <InputError message={form.errors.email} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="phone">
                            Phone number{' '}
                            <span className="text-xs text-muted-foreground">
                                (optional)
                            </span>
                        </Label>
                        <PhoneInput
                            id="phone"
                            tabIndex={3}
                            value={form.data.phone}
                            onChange={(value) => form.setData('phone', value)}
                            error={form.errors.phone}
                        />
                        <InputError message={form.errors.phone} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">Password</Label>
                        <PasswordInput
                            id="password"
                            required
                            tabIndex={4}
                            autoComplete="new-password"
                            value={form.data.password}
                            onChange={(event) =>
                                form.setData('password', event.target.value)
                            }
                            placeholder="Password"
                        />
                        <InputError message={form.errors.password} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">
                            Confirm password
                        </Label>
                        <PasswordInput
                            id="password_confirmation"
                            required
                            tabIndex={5}
                            autoComplete="new-password"
                            value={form.data.password_confirmation}
                            onChange={(event) =>
                                form.setData(
                                    'password_confirmation',
                                    event.target.value,
                                )
                            }
                            placeholder="Confirm password"
                        />
                        <InputError
                            message={form.errors.password_confirmation}
                        />
                    </div>

                    <Button
                        type="submit"
                        className="mt-2 w-full"
                        tabIndex={6}
                        data-test="register-user-button"
                        disabled={form.processing}
                    >
                        {form.processing && <Spinner />}
                        Create account
                    </Button>
                </div>

                <div className="text-center text-sm text-muted-foreground">
                    Already have an account?{' '}
                    <TextLink href={login()} tabIndex={7}>
                        Log in
                    </TextLink>
                </div>
            </form>
        </>
    );
}

Register.layout = {
    title: 'Create an account',
    description: 'Enter your details below to create your account',
};
