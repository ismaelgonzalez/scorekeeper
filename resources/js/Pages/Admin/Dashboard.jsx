import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';
import { CalendarDays, CalendarClock, Trophy, Users, UsersRound } from 'lucide-react';

const statistics = [
    { label: 'Active Leagues', icon: Trophy, color: 'text-admin-accent' },
    { label: 'Total Teams', icon: Users, color: 'text-admin-success' },
    { label: 'Registered Players', icon: UsersRound, color: 'text-admin-text' },
    { label: 'Upcoming Games', icon: CalendarDays, color: 'text-admin-accent' },
];

export default function AdminDashboard() {
    return (
        <AdminLayout title="Dashboard">
            <Head title="Admin Dashboard" />
            <div className="flex flex-col gap-8">
                <section aria-labelledby="overview-heading">
                    <p className="mb-2 text-xs font-semibold uppercase tracking-widest text-admin-accent">Administration / Overview</p>
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <h2 id="overview-heading" className="text-3xl font-semibold tracking-tight">Your game. At a glance.</h2>
                        <span className="rounded-full border border-admin-accent/30 bg-admin-accent/10 px-3 py-1 text-xs font-medium text-admin-accent">Dashboard preview</span>
                    </div>
                    <p className="mt-3 max-w-2xl text-sm leading-6 text-admin-muted">Welcome to ScoreKeeper administration. This is a dashboard scaffold; statistics and game schedules are not connected to application data yet.</p>
                </section>
                <section aria-label="Statistics preview" className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {statistics.map(({ label, icon: Icon, color }) => (
                        <article key={label} className="min-w-0 rounded-xl border border-admin-border bg-admin-card p-5">
                            <div className="flex items-center justify-between gap-3"><h3 className="text-sm font-medium">{label}</h3><Icon aria-hidden="true" className={`size-5 shrink-0 ${color}`} /></div>
                            <p className="mt-5 text-4xl font-semibold" aria-label="Not connected">—</p>
                            <p className="mt-3 text-xs text-admin-muted">Placeholder · Not connected</p>
                        </article>
                    ))}
                </section>
                <section aria-labelledby="upcoming-games-heading" className="overflow-hidden rounded-xl border border-admin-border bg-admin-card">
                    <div className="flex flex-wrap items-center justify-between gap-2 border-b border-admin-border px-6 py-5"><h2 id="upcoming-games-heading" className="font-semibold">Upcoming Games</h2><span className="text-xs text-admin-muted">Placeholder section</span></div>
                    <div className="flex flex-col items-center gap-3 px-6 py-16 text-center">
                        <div className="rounded-2xl bg-admin-background p-4 text-admin-accent"><CalendarClock aria-hidden="true" className="size-8" /></div>
                        <h3 className="mt-2 font-medium">Your next matchups will appear here</h3>
                        <p className="max-w-sm text-sm leading-6 text-admin-muted">The game schedule has not been connected yet. This preview does not represent real games or an empty schedule.</p>
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}
