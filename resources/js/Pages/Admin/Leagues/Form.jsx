import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';

const fieldClass = 'w-full rounded-md border-admin-border bg-admin-background text-admin-text focus:border-admin-accent focus:ring-admin-accent';

export default function LeagueForm({ league, statuses }) {
    const title = league ? 'Edit League' : 'Add League';
    const { data, setData, post, patch, errors, processing } = useForm({
        name: league?.name ?? '',
        description: league?.description ?? '',
        status: league?.status ?? 'draft',
    });

    function submit(event) {
        event.preventDefault();

        if (league) {
            patch(route('admin.leagues.update', league.id));
        } else {
            post(route('admin.leagues.store'));
        }
    }

    return (
        <AdminLayout title={title}>
            <Head title={title} />
            <form onSubmit={submit} className="flex max-w-2xl flex-col gap-5 rounded-xl border border-admin-border bg-admin-card p-4 sm:p-6">
                <div className="space-y-2">
                    <InputLabel htmlFor="name" value="Name" />
                    <TextInput
                        id="name"
                        className="block w-full"
                        value={data.name}
                        onChange={(event) => setData('name', event.target.value)}
                        required
                        maxLength={255}
                        autoFocus
                        aria-invalid={Boolean(errors.name)}
                        aria-describedby={errors.name ? 'name-error' : undefined}
                    />
                    <InputError id="name-error" message={errors.name} />
                </div>

                <div className="space-y-2">
                    <InputLabel htmlFor="description" value="Description (optional)" />
                    <textarea
                        id="description"
                        rows={5}
                        maxLength={16000}
                        className={fieldClass}
                        value={data.description}
                        onChange={(event) => setData('description', event.target.value)}
                        aria-invalid={Boolean(errors.description)}
                        aria-describedby={errors.description ? 'description-error' : undefined}
                    />
                    <InputError id="description-error" message={errors.description} />
                </div>

                <div className="space-y-2">
                    <InputLabel htmlFor="status" value="Status" />
                    <select
                        id="status"
                        required
                        className={`${fieldClass} capitalize`}
                        value={data.status}
                        onChange={(event) => setData('status', event.target.value)}
                        aria-invalid={Boolean(errors.status)}
                        aria-describedby={errors.status ? 'status-error' : undefined}
                    >
                        {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
                    </select>
                    <InputError id="status-error" message={errors.status} />
                </div>

                <div className="flex flex-wrap items-center gap-4">
                    <PrimaryButton disabled={processing}>{league ? 'Save Changes' : 'Create League'}</PrimaryButton>
                    <Link href={route('admin.leagues.index')} className="text-sm text-admin-muted hover:text-admin-text">Cancel</Link>
                </div>
            </form>
        </AdminLayout>
    );
}
