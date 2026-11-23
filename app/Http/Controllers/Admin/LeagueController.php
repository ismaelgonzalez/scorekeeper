<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeagueRequest;
use App\Http\Requests\UpdateLeagueRequest;
use App\Models\League;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class LeagueController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $search = is_string($search) ? Str::limit(trim($search), 255, '') : '';
        $status = $request->query('status');

        if (! in_array($status, League::STATUSES, true)) {
            $status = '';
        }

        $leagues = League::query()
            ->when($search !== '', fn ($query) => $query->whereLike('name', '%'.$search.'%'))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->orderByDesc('id')
            ->paginate(15, ['id', 'name', 'status', 'created_at'])
            ->appends(array_filter(['search' => $search, 'status' => $status], fn (string $value): bool => $value !== ''));

        return Inertia::render('Admin/Leagues/Index', [
            'leagues' => $leagues,
            'filters' => ['search' => $search, 'status' => $status],
            'statuses' => League::STATUSES,
            'can' => [
                'create' => $request->user()->can('create leagues'),
                'edit' => $request->user()->can('edit leagues'),
                'delete' => $request->user()->can('delete leagues'),
            ],
            'success' => $request->session()->get('success'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Leagues/Form', [
            'statuses' => League::STATUSES,
            'league' => null,
        ]);
    }

    public function store(StoreLeagueRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $attributes['description'] ??= '';
        $attributes['created_by'] = $request->user()->getKey();

        $base = Str::slug($attributes['name']) ?: 'league';

        for ($number = 1; ; $number++) {
            $suffix = $number === 1 ? '' : '-'.$number;
            $slug = rtrim(substr($base, 0, 255 - strlen($suffix)), '-').$suffix;

            $league = League::withTrashed()->createOrFirst(['slug' => $slug], $attributes);

            if ($league->wasRecentlyCreated) {
                break;
            }
        }

        return to_route('admin.leagues.index')->with('success', 'League created.');
    }

    public function edit(League $league): Response
    {
        return Inertia::render('Admin/Leagues/Form', [
            'statuses' => League::STATUSES,
            'league' => $league->only(['id', 'name', 'description', 'status']),
        ]);
    }

    public function update(UpdateLeagueRequest $request, League $league): RedirectResponse
    {
        $attributes = $request->validated();

        if (array_key_exists('description', $attributes)) {
            $attributes['description'] ??= '';
        }

        $league->update($attributes);

        return to_route('admin.leagues.index')->with('success', 'League updated.');
    }

    public function destroy(League $league): RedirectResponse
    {
        $league->delete();

        return to_route('admin.leagues.index')->with('success', 'League deleted.');
    }
}
