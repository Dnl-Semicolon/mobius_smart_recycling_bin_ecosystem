<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows sidebar with navigation on admin dashboard', function () {
    $page = visit(route('admin.dashboard'));

    $page->assertVisible('#admin-sidebar')
        ->assertSee('Mobius')
        ->assertSeeIn('#admin-sidebar', 'Dashboard')
        ->assertSeeIn('#admin-sidebar', 'Outlets')
        ->assertSeeIn('#admin-sidebar', 'Bins')
        ->assertSeeIn('#admin-sidebar', 'Detection Events')
        ->assertNoJavascriptErrors();
});

it('sidebar toggle button exists', function () {
    $page = visit(route('admin.dashboard'));

    $page->assertPresent('[data-testid="sidebar-toggle"]')
        ->assertNoJavascriptErrors();
});

it('sidebar contains correct navigation links', function () {
    $page = visit(route('admin.dashboard'));

    $page->assertPresent('#admin-sidebar a[href*="outlets"]')
        ->assertPresent('#admin-sidebar a[href*="bins"]')
        ->assertPresent('#admin-sidebar a[href*="detection-events"]')
        ->assertNoJavascriptErrors();
});

it('mobile menu button is visible on small viewport', function () {
    $page = visit(route('admin.dashboard'))
        ->resize(375, 812);

    $page->assertVisible('[data-testid="mobile-menu-button"]')
        ->assertNoJavascriptErrors();
});

it('admin pages have no javascript errors', function () {
    $pages = visit([
        route('admin.dashboard'),
        route('admin.outlets.index'),
        route('admin.bins.index'),
        route('admin.detection-events.index'),
    ]);

    $pages->assertNoJavascriptErrors();
});
