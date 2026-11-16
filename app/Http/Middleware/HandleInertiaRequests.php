<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'canAccessAdmin' => $request->user()?->can('access admin') ?? false,
            ],
            'appVersion' => config('app.version'),
            'adminNavigation' => fn (): array => $this->adminNavigation($request),
        ];
    }

    /**
     * @return list<array{key: string, label: string, route: ?string}>
     */
    private function adminNavigation(Request $request): array
    {
        if (! $request->user()?->can('access admin')) {
            return [];
        }

        $items = [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'admin.dashboard', 'ability' => 'access admin'],
            ['key' => 'leagues', 'label' => 'Leagues', 'route' => 'admin.leagues.index', 'ability' => 'view leagues'],
            ['key' => 'teams', 'label' => 'Teams', 'route' => 'admin.teams.index', 'ability' => 'view teams'],
            ['key' => 'players', 'label' => 'Players', 'route' => 'admin.players.index', 'ability' => 'view players'],
            ['key' => 'games', 'label' => 'Games', 'route' => 'admin.games.index', 'ability' => 'view games'],
            ['key' => 'venues', 'label' => 'Venues', 'route' => 'admin.venues.index', 'ability' => 'view venues'],
            ['key' => 'statistics', 'label' => 'Statistics', 'route' => 'admin.statistics.index', 'ability' => 'view statistics'],
            ['key' => 'settings', 'label' => 'Settings', 'route' => 'admin.settings.index', 'ability' => 'manage settings'],
        ];

        return collect($items)
            ->filter(fn (array $item): bool => ! Route::has($item['route']) || $request->user()->can($item['ability']))
            ->map(fn (array $item): array => [
                'key' => $item['key'],
                'label' => $item['label'],
                'route' => Route::has($item['route']) ? $item['route'] : null,
            ])
            ->values()
            ->all();
    }
}
