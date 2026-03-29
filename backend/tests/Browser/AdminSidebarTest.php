<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('shows sidebar with navigation when opened', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $page = visit(route('admin.dashboard'));

    // Sidebar exists in DOM but is hidden (overlay)
    $page->assertPresent('#admin-sidebar')
        ->assertSeeIn('#admin-sidebar', 'Dashboard')
        ->assertSeeIn('#admin-sidebar', 'Outlets')
        ->assertSeeIn('#admin-sidebar', 'Bins')
        ->assertSeeIn('#admin-sidebar', 'Detections')
        ->assertNoJavascriptErrors();
});

test('sidebar toggle button exists in top bar', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $page = visit(route('admin.dashboard'));

    $page->assertPresent('[data-testid="sidebar-toggle"]')
        ->assertNoJavascriptErrors();
});

test('sidebar contains correct navigation links', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $page = visit(route('admin.dashboard'));

    $page->assertPresent('#admin-sidebar a[href*="outlets"]')
        ->assertPresent('#admin-sidebar a[href*="bins"]')
        ->assertPresent('#admin-sidebar a[href*="detection-events"]')
        ->assertNoJavascriptErrors();
});

test('sidebar opens when toggle is clicked', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $page = visit(route('admin.dashboard'));

    $page->click('[data-testid="sidebar-toggle"]')
        ->assertVisible('#admin-sidebar')
        ->assertNoJavascriptErrors();
});

test('admin pages have no javascript errors', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $pages = visit([
        route('admin.dashboard'),
        route('admin.outlets.index'),
        route('admin.bins.index'),
        route('admin.detection-events.index'),
    ]);

    $pages->assertNoJavascriptErrors();
});
