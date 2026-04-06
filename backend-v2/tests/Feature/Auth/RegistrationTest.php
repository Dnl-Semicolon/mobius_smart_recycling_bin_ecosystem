<?php

use App\Enums\UserRole;
use App\Mail\RegisterEmailOtpMail;
use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\Features;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    Mail::fake();

    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'Test@Example.COM ',
        'phone' => '012-345 6789',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('register.otp.show', absolute: false));

    $user = User::first();

    expect($user)->not->toBeNull();
    expect($user->email)->toBe('test@example.com');
    expect($user->phone)->toBe('+60123456789');
    expect($user->getRolesArray())->toBe([UserRole::PublicUser->value]);
    expect($user->email_verified_at)->toBeNull();

    Mail::assertSent(RegisterEmailOtpMail::class, function (RegisterEmailOtpMail $mail) use ($user) {
        return $mail->hasTo($user->email) && $mail->user->is($user) && strlen($mail->code) === 6;
    });
});

test('registration rejects duplicate canonical phone numbers', function () {
    User::factory()->create([
        'phone' => '+60123456789',
    ]);

    $response = $this
        ->from(route('register'))
        ->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'other@example.com',
            'phone' => '012-345 6789',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    $response
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('phone');
});

test('registration rejects contact details that already exist as a lead', function () {
    RegistrationRequest::create([
        'company_name' => 'Existing Lead Co',
        'contact_name' => 'Lead Owner',
        'contact_email' => 'lead@example.com',
        'contact_phone' => '+60123456789',
        'type' => 'beverage_company',
        'status' => 'pending',
    ]);

    $response = $this
        ->from(route('register'))
        ->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'Lead@Example.COM ',
            'phone' => '012-345 6789',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    $response
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors([
            'email' => 'Please contact admin to continue with this existing lead.',
            'phone' => 'Please contact admin to continue with this existing lead.',
        ]);
});

test('registered users can verify email otp', function () {
    Mail::fake();

    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'Test@Example.COM ',
        'phone' => '012-345 6789',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::first();
    $code = null;

    Mail::assertSent(RegisterEmailOtpMail::class, function (RegisterEmailOtpMail $mail) use ($user, &$code) {
        $code = $mail->code;

        return $mail->hasTo($user->email) && $mail->user->is($user);
    });

    expect($code)->not->toBeNull();

    $response = $this->post(route('register.otp.verify'), [
        'code' => $code,
    ]);

    $response->assertRedirect(route('public.dashboard', absolute: false));

    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

test('public users without verified email are redirected to otp screen', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('public.dashboard'));

    $response->assertRedirect(route('register.otp.show', absolute: false));
});
