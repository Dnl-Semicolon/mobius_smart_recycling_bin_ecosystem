<?php

test('the application redirects to admin dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('admin.dashboard'));
});
