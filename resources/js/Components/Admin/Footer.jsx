import { usePage } from '@inertiajs/react';

export default function Footer() {
    const { appVersion } = usePage().props;

    return (
        <footer className="flex flex-wrap items-center justify-between gap-2 border-t border-admin-border px-4 py-4 text-xs text-admin-muted sm:px-8">
            <p>© {new Date().getFullYear()} ScoreKeeper. All rights reserved.</p>
            {appVersion && <p>Version {appVersion}</p>}
        </footer>
    );
}
