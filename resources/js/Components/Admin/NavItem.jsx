import { Link } from '@inertiajs/react';

export default function NavItem({ label, icon: Icon, href, active = false, collapsed = false, onNavigate }) {
    const classes = `flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-medium transition-colors ${active ? 'bg-admin-accent/10 text-admin-accent' : 'text-admin-text hover:bg-admin-card'} focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-admin-accent`;
    const content = <>
        <Icon aria-hidden="true" className="size-5 shrink-0" />
        <span className={collapsed ? 'sr-only' : 'truncate'}>{label}</span>
        {!href && !collapsed && <span className="ml-auto text-xs text-admin-muted">Soon</span>}
    </>;

    return (
        <li>
            {href ? (
                <Link href={href} onClick={onNavigate} aria-current={active ? 'page' : undefined} title={collapsed ? label : undefined} className={classes}>
                    {content}
                </Link>
            ) : (
                <span aria-disabled="true" title={`${label} — Coming soon`} className={`${classes} cursor-default opacity-60`}>
                    {content}
                    <span className="sr-only"> — Coming soon</span>
                </span>
            )}
        </li>
    );
}
