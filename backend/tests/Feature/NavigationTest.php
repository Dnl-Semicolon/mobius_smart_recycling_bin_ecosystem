<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('persons index back button links to home', function () {
    $response = $this->get('/persons');

    $response->assertStatus(200);
    $response->assertSee('href="'.route('home').'"', false);
});

test('persons create back button links to persons index not api', function () {
    $response = $this->get('/persons/create');

    $response->assertStatus(200);

    // Should link to web route, not API
    $response->assertSee('href="'.route('persons.index').'"', false);
    $response->assertDontSee('/api/', false);
});

test('route helper returns correct web route not api route', function () {
    expect(route('persons.index'))->toContain('/persons')
        ->not->toContain('/api/');
});
