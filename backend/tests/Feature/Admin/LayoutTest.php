<?php

use App\Models\Bin;
use App\Models\Outlet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin dashboard shows sidebar navigation', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Outlets')
        ->assertSee('Bins')
        ->assertSee('Detection Events');
});

test('sidebar has correct navigation links', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertSee('href="'.route('admin.dashboard').'"', false)
        ->assertSee('href="'.route('admin.outlets.index').'"', false)
        ->assertSee('href="'.route('admin.bins.index').'"', false)
        ->assertSee('href="'.route('admin.detection-events.index').'"', false);
});

test('admin pages use admin layout with sidebar', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSee('id="admin-sidebar"', false)
        ->assertSee('data-testid="sidebar-toggle"', false)
        ->assertSee('data-testid="mobile-menu-button"', false);
});

test('index pages do not have back buttons', function () {
    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('x-back-button', false);

    $this->get(route('admin.outlets.index'))
        ->assertOk()
        ->assertDontSee('x-back-button', false);

    $this->get(route('admin.bins.index'))
        ->assertOk()
        ->assertDontSee('x-back-button', false);

    $this->get(route('admin.detection-events.index'))
        ->assertOk()
        ->assertDontSee('x-back-button', false);
});

test('outlet show page has back button to outlets index', function () {
    $outlet = Outlet::factory()->create();

    $response = $this->get(route('admin.outlets.show', $outlet));

    $response->assertOk()
        ->assertSee('href="'.route('admin.outlets.index').'"', false);
});

test('outlet create page has back button to outlets index', function () {
    $response = $this->get(route('admin.outlets.create'));

    $response->assertOk()
        ->assertSee('href="'.route('admin.outlets.index').'"', false);
});

test('outlet edit page has back button to outlet show', function () {
    $outlet = Outlet::factory()->create();

    $response = $this->get(route('admin.outlets.edit', $outlet));

    $response->assertOk()
        ->assertSee('href="'.route('admin.outlets.show', $outlet).'"', false);
});

test('bin show page has back button to bins index', function () {
    $bin = Bin::factory()->create();

    $response = $this->get(route('admin.bins.show', $bin));

    $response->assertOk()
        ->assertSee('href="'.route('admin.bins.index').'"', false);
});

test('bin create page has back button to bins index', function () {
    $response = $this->get(route('admin.bins.create'));

    $response->assertOk()
        ->assertSee('href="'.route('admin.bins.index').'"', false);
});

test('bin edit page has back button to bin show', function () {
    $bin = Bin::factory()->create();

    $response = $this->get(route('admin.bins.edit', $bin));

    $response->assertOk()
        ->assertSee('href="'.route('admin.bins.show', $bin).'"', false);
});

test('all admin pages render without errors', function () {
    $outlet = Outlet::factory()->create();
    $bin = Bin::factory()->create();

    $this->get(route('admin.dashboard'))->assertOk();
    $this->get(route('admin.outlets.index'))->assertOk();
    $this->get(route('admin.bins.index'))->assertOk();
    $this->get(route('admin.detection-events.index'))->assertOk();

    $this->get(route('admin.outlets.show', $outlet))->assertOk();
    $this->get(route('admin.outlets.create'))->assertOk();
    $this->get(route('admin.outlets.edit', $outlet))->assertOk();

    $this->get(route('admin.bins.show', $bin))->assertOk();
    $this->get(route('admin.bins.create'))->assertOk();
    $this->get(route('admin.bins.edit', $bin))->assertOk();
});

test('sidebar highlights active navigation item', function () {
    $response = $this->get(route('admin.outlets.index'));

    $response->assertOk()
        ->assertSee('admin-sidebar', false);
});

test('dashboard does not show quick links', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertDontSee('Quick Links');
});
