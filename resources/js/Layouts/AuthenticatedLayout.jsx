import Footer from '@/Components/Admin/Footer';
import Sidebar from '@/Components/Admin/Sidebar';
import Topbar from '@/Components/Admin/Topbar';
import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function AuthenticatedLayout({ title = 'Dashboard', header, children }) {
    const { url } = usePage();
    const [collapsed, setCollapsed] = useState(() => {
        try {
            return typeof window !== 'undefined' && window.localStorage.getItem('scorekeeper.admin.sidebar-collapsed') === 'true';
        } catch {
            return false;
        }
    });
    const [mobileOpen, setMobileOpen] = useState(false);

    useEffect(() => {
        try {
            window.localStorage.setItem('scorekeeper.admin.sidebar-collapsed', String(collapsed));
        } catch {
            // The sidebar still works when browser storage is unavailable.
        }
    }, [collapsed]);

    useEffect(() => setMobileOpen(false), [url]);

    useEffect(() => {
        const desktop = window.matchMedia('(min-width: 768px)');
        const closeOnDesktop = () => {
            if (desktop.matches) {
                setMobileOpen(false);
            }
        };
        desktop.addEventListener('change', closeOnDesktop);
        return () => desktop.removeEventListener('change', closeOnDesktop);
    }, []);

    return (
        <div className="app-theme flex h-dvh overflow-hidden bg-admin-background text-admin-text [color-scheme:dark]">
            <a href="#admin-content" className="sr-only fixed left-4 top-4 z-[60] rounded bg-admin-accent px-4 py-2 text-admin-sidebar focus:not-sr-only">Skip to content</a>
            <Sidebar collapsed={collapsed} mobileOpen={mobileOpen} onClose={() => setMobileOpen(false)} />
            <div className="flex min-w-0 flex-1 flex-col">
                <Topbar title={title} collapsed={collapsed} mobileOpen={mobileOpen} onToggleDesktop={() => setCollapsed((value) => !value)} onToggleMobile={() => setMobileOpen(true)} />
                <div className="min-h-0 flex-1 overflow-y-auto overflow-x-hidden">
                    <main id="admin-content" tabIndex={-1} className="mx-auto min-h-[calc(100%-4rem)] w-full max-w-7xl p-4 sm:p-8">{header && <div className="mb-6">{header}</div>}{children}</main>
                    <Footer />
                </div>
            </div>
        </div>
    );
}
