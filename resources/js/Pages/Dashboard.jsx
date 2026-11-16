import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Dashboard() {
    const { user, canAccessAdmin } = usePage().props.auth;

    return (
        <AuthenticatedLayout title="Dashboard">
            <Head title="Dashboard" />
            <section className="rounded-xl border border-admin-border bg-admin-card p-6 sm:p-8">
                <p className="text-xs font-semibold uppercase tracking-widest text-admin-accent">ScoreKeeper</p>
                <h2 className="mt-3 text-2xl font-semibold">Welcome, {user.name}</h2>
                <p className="mt-3 text-sm text-admin-muted">You're logged in! Manage your profile and explore your ScoreKeeper workspace.</p>
                <div className="mt-6 flex flex-wrap gap-4 text-sm">
                    <Link href={route('profile.edit')} className="rounded-lg border border-admin-border px-4 py-2 hover:bg-admin-topbar focus-visible:outline-2 focus-visible:outline-admin-accent">Your profile</Link>
                    {canAccessAdmin && <Link href={route('admin.dashboard')} className="rounded-lg bg-admin-accent px-4 py-2 font-medium text-admin-sidebar focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-admin-accent">Open administration</Link>}
                </div>
            </section>
        </AuthenticatedLayout>
    );
}
