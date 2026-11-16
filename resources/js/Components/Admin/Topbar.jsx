import { Menu, MenuButton, MenuItem, MenuItems, Popover, PopoverButton, PopoverPanel } from '@headlessui/react';
import { Link, usePage } from '@inertiajs/react';
import { Bell, ChevronDown, LogOut, Menu as MenuIcon, PanelLeftClose, PanelLeftOpen, UserRound } from 'lucide-react';

const controlClasses = 'rounded-lg p-2 text-admin-text hover:bg-admin-card focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-admin-accent';

export default function Topbar({ title, collapsed, mobileOpen, onToggleDesktop, onToggleMobile }) {
    const { user } = usePage().props.auth;
    const ToggleIcon = collapsed ? PanelLeftOpen : PanelLeftClose;

    return (
        <header className="flex h-16 shrink-0 items-center justify-between gap-3 border-b border-admin-border bg-admin-topbar px-4 sm:px-6">
            <div className="flex min-w-0 items-center gap-3">
                <button type="button" onClick={onToggleDesktop} aria-label={collapsed ? 'Expand sidebar' : 'Collapse sidebar'} aria-expanded={!collapsed} aria-controls="admin-sidebar" className={`${controlClasses} hidden md:block`}><ToggleIcon aria-hidden="true" className="size-5" /></button>
                <button type="button" onClick={onToggleMobile} aria-label="Open navigation" aria-expanded={mobileOpen} aria-controls="admin-mobile-sidebar" className={`${controlClasses} md:hidden`}><MenuIcon aria-hidden="true" className="size-5" /></button>
                <h1 className="truncate text-base font-semibold">{title}</h1>
            </div>
            <div className="flex shrink-0 items-center gap-2 sm:gap-4">
                <Popover className="relative">
                    <PopoverButton aria-label="Notifications" className={controlClasses}><Bell aria-hidden="true" className="size-5" /></PopoverButton>
                    <PopoverPanel className="absolute right-0 z-30 mt-3 w-64 max-w-[calc(100vw-2rem)] rounded-xl border border-admin-border bg-admin-card p-4 shadow-xl">
                        <h2 className="font-semibold">Notifications</h2>
                        <p className="mt-2 text-sm text-admin-muted">Notifications are not connected yet.</p>
                    </PopoverPanel>
                </Popover>
                <Menu>
                    <MenuButton className={`flex max-w-48 items-center gap-2 ${controlClasses}`}>
                        <span className="flex size-8 shrink-0 items-center justify-center rounded-full bg-admin-accent/15 font-semibold text-admin-accent" aria-hidden="true">{user.name.slice(0, 1).toUpperCase()}</span>
                        <span className="hidden truncate text-sm sm:block">{user.name}</span>
                        <span className="sr-only">Account menu</span>
                        <ChevronDown aria-hidden="true" className="size-4 shrink-0" />
                    </MenuButton>
                    <MenuItems anchor="bottom end" className="z-50 mt-2 w-52 rounded-xl border border-admin-border bg-admin-card p-1 text-admin-text shadow-xl focus:outline-none">
                        <MenuItem><Link href={route('profile.edit')} className="flex items-center gap-3 rounded-lg px-3 py-2 text-sm data-focus:bg-admin-topbar data-focus:text-admin-accent"><UserRound aria-hidden="true" className="size-4" />Profile</Link></MenuItem>
                        <MenuItem><Link href={route('logout')} method="post" as="button" className="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-admin-danger data-focus:bg-admin-topbar"><LogOut aria-hidden="true" className="size-4" />Log out</Link></MenuItem>
                    </MenuItems>
                </Menu>
            </div>
        </header>
    );
}
