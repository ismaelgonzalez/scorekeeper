import NavItem from '@/Components/Admin/NavItem';
import { Dialog, DialogBackdrop, DialogPanel, DialogTitle } from '@headlessui/react';
import { usePage } from '@inertiajs/react';
import { BarChart3, CalendarDays, LayoutDashboard, MapPin, Settings, Shield, Trophy, UserRound, Users, UsersRound, X } from 'lucide-react';

const icons = { dashboard: LayoutDashboard, profile: UserRound, leagues: Trophy, teams: Users, players: UsersRound, games: CalendarDays, venues: MapPin, statistics: BarChart3, settings: Settings };

export default function Sidebar({ collapsed, mobileOpen, onClose }) {
    const { adminNavigation = [] } = usePage().props;
    const accountNavigation = [
        { key: 'dashboard', label: 'Dashboard', route: 'dashboard' },
        { key: 'profile', label: 'Profile', route: 'profile.edit' },
    ];
    const navigationItems = (items, compact) => items.map((item) => (
        <NavItem key={item.key} label={item.label} icon={icons[item.key]} href={item.route ? route(item.route) : null} active={item.route ? route().current(item.route.endsWith('.index') ? `${item.route.slice(0, -6)}.*` : item.route) : false} collapsed={compact} onNavigate={onClose} />
    ));
    const navigation = (compact) => (
        <nav aria-label="Main navigation" className="p-3">
            <ul className="space-y-1">
                {navigationItems(accountNavigation, compact)}
            </ul>
            {adminNavigation.length > 0 && <>
                <p className={`mt-6 mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-admin-muted ${compact ? 'sr-only' : ''}`}>Administration</p>
                <ul className="space-y-1">{navigationItems(adminNavigation.map((item) => item.key === 'dashboard' ? { ...item, label: 'Admin Dashboard' } : item), compact)}</ul>
            </>}
        </nav>
    );
    const brand = (compact) => (
        <div className="flex h-16 shrink-0 items-center gap-3 border-admin-border px-6">
            <Shield aria-hidden="true" className="size-6 shrink-0 text-admin-accent" />
            <span className={compact ? 'sr-only' : 'font-semibold tracking-tight'}>ScoreKeeper<span className="ml-1 text-admin-accent">.</span></span>
        </div>
    );

    return <>
        <aside id="admin-sidebar" aria-label="ScoreKeeper sidebar" className={`hidden shrink-0 flex-col border-r border-admin-border bg-admin-sidebar md:flex ${collapsed ? 'w-20' : 'w-64'}`}>
            {brand(collapsed)}
            <div className="min-h-0 flex-1 overflow-y-auto">{navigation(collapsed)}</div>
            {!collapsed && <p className="border-admin-border p-5 text-xs text-admin-muted">ScoreKeeper workspace</p>}
        </aside>
        <Dialog open={mobileOpen} onClose={onClose} className="relative z-50 text-admin-text md:hidden">
            <DialogBackdrop transition className="fixed inset-0 bg-black/70 transition-opacity duration-200 data-closed:opacity-0 motion-reduce:transition-none" />
            <DialogPanel transition id="admin-mobile-sidebar" className="fixed inset-y-0 left-0 flex w-72 max-w-[calc(100%-2rem)] flex-col bg-admin-sidebar shadow-xl transition-transform duration-200 data-closed:-translate-x-full motion-reduce:transition-none">
                <DialogTitle className="sr-only">ScoreKeeper navigation</DialogTitle>
                {brand(false)}
                <button type="button" data-autofocus onClick={onClose} aria-label="Close navigation" className="absolute right-2 top-4 rounded p-1 text-admin-text focus-visible:outline-2 focus-visible:outline-admin-accent"><X aria-hidden="true" className="size-5" /></button>
                <div className="min-h-0 flex-1 overflow-y-auto">{navigation(false)}</div>
            </DialogPanel>
        </Dialog>
    </>;
}
