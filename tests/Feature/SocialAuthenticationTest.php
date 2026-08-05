<?php

use App\Models\SocialAccount;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

test('guests can start social authentication', function (string $provider) {
    Socialite::fake($provider);

    $this->get(route('social.redirect', $provider))->assertRedirect();
})->with(['google', 'apple']);

test('a verified social account creates and authenticates a user', function () {
    Socialite::fake('google', socialiteUser([
        'id' => 'google-user-1',
        'name' => 'Ada Lovelace',
        'email' => 'ADA@example.com',
        'verified_email' => true,
    ]));

    $response = $this->get(route('social.callback', 'google'));

    $user = User::query()->where('email', 'ada@example.com')->firstOrFail();

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
    expect($user->email_verified_at)->not->toBeNull();
    $this->assertDatabaseHas('social_accounts', [
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_user_id' => 'google-user-1',
    ]);
});

test('a verified social account is linked to an existing user by email', function () {
    $user = User::factory()->create(['email' => 'person@example.com']);
    Socialite::fake('apple', socialiteUser([
        'id' => 'apple-user-1',
        'email' => 'person@example.com',
        'email_verified' => 'true',
    ]));

    $this->post(route('social.callback', 'apple'));

    $this->assertAuthenticatedAs($user);
    expect(User::query()->count())->toBe(1)
        ->and($user->socialAccounts()->first())
        ->toBeInstanceOf(SocialAccount::class);
});

test('social authentication rejects an unverified email address', function () {
    Socialite::fake('google', socialiteUser([
        'email' => 'unverified@example.com',
        'verified_email' => false,
    ]));

    $response = $this->get(route('social.callback', 'google'));

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('social');
    $this->assertDatabaseMissing('users', ['email' => 'unverified@example.com']);
});

test('social authentication requires the challenge when two factor is enabled', function () {
    $user = User::factory()->withTwoFactor()->create(['email' => 'secure@example.com']);
    Socialite::fake('google', socialiteUser([
        'id' => 'google-secure-user',
        'email' => 'secure@example.com',
        'verified_email' => true,
    ]));

    $response = $this->get(route('social.callback', 'google'));

    $this->assertGuest();
    $response->assertRedirect(route('two-factor.login'));
    $response->assertSessionHas('login.id', $user->id);
});

function socialiteUser(array $attributes): SocialiteUser
{
    return SocialiteUser::fake($attributes);
}
