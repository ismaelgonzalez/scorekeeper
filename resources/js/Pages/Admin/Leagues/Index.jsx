import DangerButton from "@/Components/DangerButton";
import InputLabel from "@/Components/InputLabel";
import Modal from "@/Components/Modal";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import TextInput from "@/Components/TextInput";
import AdminLayout from "@/Layouts/AdminLayout";
import StatusBadge from "@/Components/StatusBadge";
import { DialogTitle } from "@headlessui/react";
import { Head, Link, router, useForm } from "@inertiajs/react";
import { useState } from "react";

const statusClasses = {
    draft: "bg-admin-muted/10 text-admin-muted",
    active: "bg-admin-success/10 text-admin-success",
    completed: "bg-admin-accent/10 text-admin-accent",
    cancelled: "bg-admin-danger/10 text-admin-danger",
};

const dateFormat = new Intl.DateTimeFormat(undefined, { dateStyle: "medium" });

export default function LeagueIndex({
    leagues,
    filters,
    statuses,
    can,
    success,
}) {
    const [pendingDeletion, setPendingDeletion] = useState(null);
    const { delete: deleteLeague, processing } = useForm({});
    const indexUrl = route("admin.leagues.index");

    function filterLeagues(event) {
        event.preventDefault();

        const fields = new FormData(event.currentTarget);
        const search = fields.get("search").trim();
        const status = fields.get("status");

        router.get(indexUrl, {
            ...(search && { search }),
            ...(status && { status }),
        });
    }

    function confirmDeletion(event) {
        event.preventDefault();

        deleteLeague(route("admin.leagues.destroy", pendingDeletion.id), {
            onSuccess: () => setPendingDeletion(null),
        });
    }

    return (
        <AdminLayout title="Leagues">
            <Head title="Leagues" />
            <div className="flex flex-col gap-5">
                {success && (
                    <p
                        role="status"
                        className="rounded-lg border border-admin-success/30 bg-admin-success/10 px-4 py-3 text-sm text-admin-success"
                    >
                        {success}
                    </p>
                )}

                <div className="flex flex-wrap items-end justify-between gap-4">
                    <form
                        key={JSON.stringify(filters)}
                        onSubmit={filterLeagues}
                        className="flex w-full flex-wrap items-end gap-3 sm:w-auto"
                    >
                        <div className="w-full space-y-2 sm:w-56">
                            <InputLabel
                                htmlFor="search"
                                value="Search by name"
                            />
                            <TextInput
                                id="search"
                                name="search"
                                type="search"
                                defaultValue={filters.search}
                                maxLength={255}
                                className="w-full"
                            />
                        </div>
                        <div className="space-y-2">
                            <InputLabel
                                htmlFor="filter-status"
                                value="Status"
                            />
                            <select
                                id="filter-status"
                                name="status"
                                defaultValue={filters.status}
                                className="rounded-md border-admin-border bg-admin-background capitalize text-admin-text focus:border-admin-accent focus:ring-admin-accent"
                            >
                                <option value="">All statuses</option>
                                {statuses.map((status) => (
                                    <option key={status} value={status}>
                                        {status}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <PrimaryButton className="min-h-10">
                            Filter
                        </PrimaryButton>
                        {(filters.search || filters.status) && (
                            <Link
                                href={indexUrl}
                                className="py-2 text-sm text-admin-muted hover:text-admin-text"
                            >
                                Clear filters
                            </Link>
                        )}
                    </form>
                    {can.create && (
                        <Link
                            href={route("admin.leagues.create")}
                            className="inline-flex min-h-10 items-center rounded-md bg-admin-accent px-4 text-sm font-semibold text-admin-sidebar hover:bg-admin-accent/90"
                        >
                            Add League
                        </Link>
                    )}
                </div>

                <section
                    aria-label="League results"
                    className="overflow-hidden rounded-xl border border-admin-border bg-admin-card"
                >
                    {leagues.data.length ? (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-admin-topbar text-admin-muted">
                                    <tr>
                                        <th
                                            scope="col"
                                            className="px-4 py-3 font-medium"
                                        >
                                            League name
                                        </th>
                                        <th
                                            scope="col"
                                            className="px-4 py-3 font-medium"
                                        >
                                            Status
                                        </th>
                                        <th
                                            scope="col"
                                            className="whitespace-nowrap px-4 py-3 font-medium"
                                        >
                                            Created date
                                        </th>
                                        {(can.edit || can.delete) && (
                                            <th
                                                scope="col"
                                                className="px-4 py-3 text-right font-medium"
                                            >
                                                Actions
                                            </th>
                                        )}
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-admin-border">
                                    {leagues.data.map((league) => (
                                        <tr key={league.id}>
                                            <th
                                                scope="row"
                                                className="min-w-36 wrap-anywhere px-4 py-3 font-medium"
                                            >
                                                {league.name}
                                            </th>
                                            <td className="px-4 py-3">
                                                <StatusBadge
                                                    status={league.status}
                                                />
                                            </td>
                                            <td className="whitespace-nowrap px-4 py-3 text-admin-muted">
                                                {league.created_at ? (
                                                    <time
                                                        dateTime={
                                                            league.created_at
                                                        }
                                                    >
                                                        {dateFormat.format(
                                                            new Date(
                                                                league.created_at,
                                                            ),
                                                        )}
                                                    </time>
                                                ) : (
                                                    "—"
                                                )}
                                            </td>
                                            {(can.edit || can.delete) && (
                                                <td className="px-4 py-3">
                                                    <div className="flex justify-end gap-4">
                                                        {can.edit && (
                                                            <Link
                                                                href={route(
                                                                    "admin.leagues.edit",
                                                                    league.id,
                                                                )}
                                                                aria-label={`Edit ${league.name}`}
                                                                className="text-admin-accent hover:underline"
                                                            >
                                                                Edit
                                                            </Link>
                                                        )}
                                                        {can.delete && (
                                                            <button
                                                                type="button"
                                                                onClick={() =>
                                                                    setPendingDeletion(
                                                                        league,
                                                                    )
                                                                }
                                                                aria-label={`Delete ${league.name}`}
                                                                className="text-admin-danger hover:underline"
                                                            >
                                                                Delete
                                                            </button>
                                                        )}
                                                    </div>
                                                </td>
                                            )}
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <p className="px-4 py-10 text-center text-sm text-admin-muted">
                            {filters.search || filters.status
                                ? "No leagues match your filters."
                                : "No leagues found."}
                        </p>
                    )}
                    {leagues.last_page > 1 && (
                        <nav
                            aria-label="League pagination"
                            className="flex flex-wrap items-center justify-between gap-3 border-t border-admin-border px-4 py-3 text-sm"
                        >
                            <p className="text-admin-muted">
                                Page {leagues.current_page} of{" "}
                                {leagues.last_page}
                            </p>
                            <div className="flex gap-4">
                                {leagues.prev_page_url && (
                                    <Link
                                        href={leagues.prev_page_url}
                                        className="text-admin-accent hover:underline"
                                    >
                                        Previous
                                    </Link>
                                )}
                                {leagues.next_page_url && (
                                    <Link
                                        href={leagues.next_page_url}
                                        className="text-admin-accent hover:underline"
                                    >
                                        Next
                                    </Link>
                                )}
                            </div>
                        </nav>
                    )}
                </section>
            </div>

            <Modal
                show={Boolean(pendingDeletion)}
                onClose={() => setPendingDeletion(null)}
                closeable={!processing}
                maxWidth="md"
                className="app-theme"
            >
                <form onSubmit={confirmDeletion} className="space-y-4 p-6">
                    <DialogTitle className="text-lg font-semibold">
                        Delete league?
                    </DialogTitle>
                    <p className="wrap-anywhere text-sm text-admin-muted">
                        Delete {pendingDeletion?.name} from the league list?
                    </p>
                    <div className="flex flex-wrap justify-end gap-3">
                        <SecondaryButton
                            onClick={() => setPendingDeletion(null)}
                            disabled={processing}
                            data-autofocus
                        >
                            Cancel
                        </SecondaryButton>
                        <DangerButton disabled={processing}>
                            Delete League
                        </DangerButton>
                    </div>
                </form>
            </Modal>
        </AdminLayout>
    );
}
