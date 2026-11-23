const statusColors = {
    draft: "bg-[#3c3836] text-[#a89984] border-[#665c54]",
    active: "bg-[#303d20] text-[#b8bb26] border-[#6b8121]",
    completed: "bg-[#49391d] text-[#fabd2f] border-[#b57614]",
    cancelled: "bg-[#492e2b] text-[#fb4934] border-[#a6382b]",
};

export default function StatusBadge({ status }) {
    const colors = statusColors[status] ?? statusColors.draft;

    return (
        <span
            className={`
                inline-flex items-center rounded-full border
                px-3 py-1 text-xs font-medium
                ${colors}
            `}
        >
            {status.charAt(0).toUpperCase() + status.slice(1)}
        </span>
    );
}
