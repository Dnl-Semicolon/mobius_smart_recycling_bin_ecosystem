<?php

test('validates required person fields', function () {
    $response = $this->postJson('/api/v1/persons', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors([
        'name',
        'birthday',
        'phone',
        'line_1',
        'city',
        'state',
        'postal_code',
    ]);
});
