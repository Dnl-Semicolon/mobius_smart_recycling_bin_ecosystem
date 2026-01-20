<?php

use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('index returns 200 with list of persons', function () {
    Person::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/persons');

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'birthday', 'phone'],
        ],
        'message',
    ]);
    $response->assertJsonCount(3, 'data');
});

test('index returns empty array when no persons exist', function () {
    $response = $this->getJson('/api/v1/persons');

    $response->assertOk();
    $response->assertJsonCount(0, 'data');
});

test('store with valid data returns 201', function () {
    $payload = [
        'name' => 'Ahmad Bin Ali',
        'birthday' => '1990-05-15',
        'phone' => '012-345 6789',
        'line_1' => '123 Jalan Ampang',
        'line_2' => 'Unit 5A',
        'city' => 'Kuala Lumpur',
        'state' => 'KL',
        'postal_code' => '50450',
    ];

    $response = $this->postJson('/api/v1/persons', $payload);

    $response->assertCreated();
    $response->assertJsonPath('data.name', 'Ahmad Bin Ali');
    $response->assertJsonPath('message', 'Person created successfully.');

    $this->assertDatabaseHas('people', [
        'name' => 'Ahmad Bin Ali',
        'phone' => '012-345 6789',
    ]);

    $this->assertDatabaseHas('addresses', [
        'line_1' => '123 Jalan Ampang',
        'city' => 'Kuala Lumpur',
    ]);
});

test('store with invalid data returns 422', function () {
    $response = $this->postJson('/api/v1/persons', [
        'name' => '',
        'birthday' => 'not-a-date',
        'phone' => 'invalid',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['name', 'birthday', 'phone']);
});

test('show returns person with address', function () {
    $person = Person::factory()->create();
    $person->addresses()->create([
        'line_1' => '456 Jalan Sultan',
        'line_2' => 'Floor 3',
        'city' => 'Petaling Jaya',
        'state' => 'Selangor',
        'postal_code' => '47301',
    ]);

    $response = $this->getJson("/api/v1/persons/{$person->id}");

    $response->assertOk();
    $response->assertJsonPath('data.id', $person->id);
    $response->assertJsonPath('data.name', $person->name);
    $response->assertJsonPath('data.addresses.0.line_1', '456 Jalan Sultan');
    $response->assertJsonPath('data.addresses.0.city', 'Petaling Jaya');
    $response->assertJsonPath('message', 'Person retrieved successfully.');
});

test('update modifies person and address', function () {
    $person = Person::factory()->create([
        'name' => 'Original Name',
    ]);
    $person->addresses()->create([
        'line_1' => 'Old Address',
        'city' => 'Old City',
        'state' => 'Johor',
        'postal_code' => '80000',
    ]);

    $payload = [
        'name' => 'Updated Name',
        'birthday' => '1985-12-25',
        'phone' => '016-789 0123',
        'line_1' => 'New Address',
        'city' => 'New City',
        'state' => 'Penang',
        'postal_code' => '10000',
    ];

    $response = $this->putJson("/api/v1/persons/{$person->id}", $payload);

    $response->assertOk();
    $response->assertJsonPath('data.name', 'Updated Name');
    $response->assertJsonPath('data.addresses.0.line_1', 'New Address');
    $response->assertJsonPath('message', 'Person updated successfully.');

    $this->assertDatabaseHas('people', [
        'id' => $person->id,
        'name' => 'Updated Name',
    ]);

    $this->assertDatabaseHas('addresses', [
        'person_id' => $person->id,
        'line_1' => 'New Address',
        'city' => 'New City',
    ]);
});

test('destroy deletes person', function () {
    $person = Person::factory()->create();
    $personId = $person->id;

    $response = $this->deleteJson("/api/v1/persons/{$person->id}");

    $response->assertOk();
    $response->assertJsonPath('message', 'Person deleted successfully.');

    $this->assertDatabaseMissing('people', [
        'id' => $personId,
    ]);
});

test('show returns 404 for missing person', function () {
    $response = $this->getJson('/api/v1/persons/99999');

    $response->assertNotFound();
});
