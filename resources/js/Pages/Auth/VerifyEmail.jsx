import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function VerifyEmail({ status }) {
    const { post, processing } = useForm({});

    const submit = (e) => {
        e.preventDefault();

        post(route('verification.send'));
    };

    return (
        <AuthenticatedLayout title="Email Verification">
            <div className="mx-auto w-full max-w-xl rounded-xl border border-admin-border bg-admin-card p-6">
                <Head title="Email Verification" />

                <div className="mb-4 text-sm text-admin-muted">
                    Thanks for signing up! Before getting started, could you verify
                    your email address by clicking on the link we just emailed to
                    you? If you didn't receive the email, we will gladly send you
                    another.
                </div>

                {status === 'verification-link-sent' && (
                    <div className="mb-4 text-sm font-medium text-admin-success">
                        A new verification link has been sent to the email address
                        you provided during registration.
                    </div>
                )}

                <form onSubmit={submit}>
                    <div className="mt-4 flex flex-wrap items-center justify-between gap-4">
                        <PrimaryButton disabled={processing}>
                            Resend Verification Email
                        </PrimaryButton>

                        <Link
                            href={route('logout')}
                            method="post"
                            as="button"
                            className="rounded-md text-sm text-admin-muted underline hover:text-admin-text focus:outline-none focus:ring-2 focus:ring-admin-accent focus:ring-offset-2"
                        >
                            Log Out
                        </Link>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
