import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function AdminLayout({ title = 'Dashboard', children }) {
    return <AuthenticatedLayout title={title}>{children}</AuthenticatedLayout>;
}
