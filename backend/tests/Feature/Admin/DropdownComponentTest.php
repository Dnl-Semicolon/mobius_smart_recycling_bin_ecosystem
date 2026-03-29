<?php

use App\Models\Bin;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('renders dropdown select component on bin create page', function () {
    $this->get(route('admin.bins.create'))
        ->assertSuccessful()
        ->assertSee('data-dropdown-select', false)
        ->assertSee('data-dropdown-trigger', false)
        ->assertSee('data-dropdown-panel', false)
        ->assertSee('Select status');
});

it('renders dropdown select component on bin edit page', function () {
    $bin = Bin::factory()->create();

    $this->get(route('admin.bins.edit', $bin))
        ->assertSuccessful()
        ->assertSee('data-dropdown-select', false)
        ->assertSee('data-dropdown-trigger', false);
});

it('renders dropdown select component on outlet create page', function () {
    $this->get(route('admin.outlets.create'))
        ->assertSuccessful()
        ->assertSee('data-dropdown-select', false)
        ->assertSee('Select status');
});

it('renders dropdown select component on outlet edit page', function () {
    $outlet = Outlet::factory()->create();

    $this->get(route('admin.outlets.edit', $outlet))
        ->assertSuccessful()
        ->assertSee('data-dropdown-select', false);
});

it('submits bin create form with dropdown-selected value', function () {
    $data = [
        'serial_number' => 'MBR-TEST-001',
        'fill_level' => 50,
        'status' => 'active',
    ];

    $this->post(route('admin.bins.store'), $data)
        ->assertRedirect();

    $this->assertDatabaseHas('bins', [
        'serial_number' => 'MBR-TEST-001',
        'status' => 'active',
    ]);
});

it('submits outlet create form with dropdown-selected contract status', function () {
    $data = [
        'name' => 'Test Outlet',
        'address' => '123 Test Street',
        'contract_status' => 'active',
    ];

    $this->post(route('admin.outlets.store'), $data)
        ->assertRedirect();

    $this->assertDatabaseHas('outlets', [
        'name' => 'Test Outlet',
        'contract_status' => 'active',
    ]);
});
