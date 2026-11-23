<?php

namespace Tests\Feature;

use App\Models\League;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class LeagueManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    #[TestWith(['League Admin'])]
    #[TestWith(['Super Admin'])]
    public function test_authorized_users_can_view_league_management(string $role): void
    {
        $user = $this->signInAs($role);
        $leagues = League::factory()->for($user, 'creator')->count(16)->create();

        $this->get(route('admin.leagues.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Leagues/Index')
                ->has('leagues.data', 15)
                ->where('leagues.data.0.name', $leagues->last()->name)
                ->where('leagues.total', 16)
                ->where('leagues.last_page', 2)
                ->where('can.create', true)
                ->where('can.edit', true)
                ->where('can.delete', true));

        $this->get(route('admin.leagues.create'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Leagues/Form')->where('league', null));

        $this->get(route('admin.leagues.edit', $leagues->first()))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Leagues/Form')->where('league.id', $leagues->first()->id));
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.leagues.index'))->assertRedirect(route('login'));
    }

    #[TestWith(['User'])]
    #[TestWith(['Team Manager'])]
    #[TestWith(['Scorekeeper'])]
    public function test_unauthorized_users_cannot_access_league_management(string $role): void
    {
        $this->signInAs($role);
        $league = League::factory()->create();

        $this->get(route('admin.leagues.index'))->assertForbidden();
        $this->get(route('admin.leagues.create'))->assertForbidden();
        $this->post(route('admin.leagues.store'))->assertForbidden();
        $this->get(route('admin.leagues.edit', $league))->assertForbidden();
        $this->patch(route('admin.leagues.update', $league))->assertForbidden();
        $this->delete(route('admin.leagues.destroy', $league))->assertForbidden();
    }

    public function test_view_permission_alone_does_not_grant_write_access(): void
    {
        $user = $this->signInAs('Scorekeeper');
        $user->givePermissionTo('view leagues');
        $league = League::factory()->create();

        $this->get(route('admin.leagues.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('can.create', false)
                ->where('can.edit', false)
                ->where('can.delete', false));

        $this->post(route('admin.leagues.store'))->assertForbidden();
        $this->patch(route('admin.leagues.update', $league))->assertForbidden();
        $this->delete(route('admin.leagues.destroy', $league))->assertForbidden();
    }

    public function test_authorized_users_can_create_leagues_and_are_recorded_as_the_creator(): void
    {
        $user = $this->signInAs('League Admin');
        $otherUser = User::factory()->create();

        $this->post(route('admin.leagues.store'), [
            'name' => 'North Valley Softball',
            'status' => 'draft',
            'slug' => 'supplied-slug',
            'created_by' => $otherUser->id,
        ])->assertRedirect(route('admin.leagues.index'))->assertSessionHas('success', 'League created.');

        $this->assertDatabaseHas('leagues', [
            'name' => 'North Valley Softball',
            'slug' => 'north-valley-softball',
            'description' => '',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);
    }

    public function test_create_and_update_validate_required_fields_and_allowed_statuses(): void
    {
        $this->signInAs('League Admin');
        $league = League::factory()->create();

        $this->post(route('admin.leagues.store'))->assertSessionHasErrors(['name', 'status']);
        $this->patch(route('admin.leagues.update', $league))->assertSessionHasErrors(['name', 'status']);

        $invalid = ['name' => str_repeat('a', 256), 'description' => ['invalid'], 'status' => 'paused'];
        $this->post(route('admin.leagues.store'), $invalid)->assertSessionHasErrors(['name', 'description', 'status']);
        $this->patch(route('admin.leagues.update', $league), $invalid)->assertSessionHasErrors(['name', 'description', 'status']);

        $this->assertDatabaseCount('leagues', 1);
        $this->assertDatabaseHas('leagues', ['id' => $league->id, 'name' => $league->name, 'status' => $league->status]);
    }

    public function test_generated_slugs_are_unique_even_when_a_league_was_deleted(): void
    {
        $user = $this->signInAs('League Admin');
        League::factory()->for($user, 'creator')->create(['slug' => 'valley-league'])->delete();

        $this->post(route('admin.leagues.store'), ['name' => 'Valley League!', 'status' => 'active'])
            ->assertRedirect(route('admin.leagues.index'));
        $this->post(route('admin.leagues.store'), ['name' => 'Valley League!', 'status' => 'draft'])
            ->assertRedirect(route('admin.leagues.index'));

        $this->assertDatabaseHas('leagues', ['name' => 'Valley League!', 'slug' => 'valley-league-2', 'status' => 'active']);
        $this->assertDatabaseHas('leagues', ['name' => 'Valley League!', 'slug' => 'valley-league-3', 'status' => 'draft']);
    }

    public function test_authorized_users_can_update_a_league_and_preserve_its_slug_and_creator(): void
    {
        $user = $this->signInAs('League Admin');
        $league = League::factory()->create(['slug' => 'original-slug']);

        $this->patch(route('admin.leagues.update', $league), [
            'name' => 'Renamed League',
            'description' => 'Updated description',
            'status' => 'completed',
            'slug' => 'changed-slug',
            'created_by' => $user->id,
        ])->assertRedirect(route('admin.leagues.index'))->assertSessionHas('success', 'League updated.');

        $this->assertDatabaseHas('leagues', [
            'id' => $league->id,
            'name' => 'Renamed League',
            'description' => 'Updated description',
            'status' => 'completed',
            'slug' => 'original-slug',
            'created_by' => $league->created_by,
        ]);
    }

    public function test_deleting_a_league_soft_deletes_it_and_hides_it_from_management(): void
    {
        $user = $this->signInAs('League Admin');
        $league = League::factory()->for($user, 'creator')->create();

        $this->delete(route('admin.leagues.destroy', $league))
            ->assertRedirect(route('admin.leagues.index'))
            ->assertSessionHas('success', 'League deleted.');

        $this->assertSoftDeleted($league);
        $this->get(route('admin.leagues.index'))->assertInertia(fn (Assert $page) => $page
            ->has('leagues.data', 0)
            ->where('success', 'League deleted.'));
        $this->get(route('admin.leagues.edit', $league))->assertNotFound();
    }

    public function test_search_and_status_filters_can_be_combined_and_survive_pagination(): void
    {
        $user = $this->signInAs('League Admin');
        $matching = League::factory()->for($user, 'creator')->count(16)->sequence(fn ($sequence) => [
            'name' => 'Valley League '.$sequence->index,
            'status' => 'active',
        ])->create();
        League::factory()->for($user, 'creator')->create(['name' => 'South Valley League', 'status' => 'draft']);
        League::factory()->for($user, 'creator')->create(['name' => 'Harbor League', 'status' => 'active']);

        $filters = ['search' => 'valley', 'status' => 'active'];

        $this->get(route('admin.leagues.index', $filters))->assertInertia(fn (Assert $page) => $page
            ->where('filters', $filters)
            ->where('leagues.total', 16)
            ->where('leagues.next_page_url', route('admin.leagues.index', [...$filters, 'page' => 2])));

        $this->get(route('admin.leagues.index', [...$filters, 'page' => 2]))->assertInertia(fn (Assert $page) => $page
            ->has('leagues.data', 1)
            ->where('leagues.data.0.id', $matching->first()->id));
    }

    private function signInAs(string $role): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);

        return $user;
    }
}
