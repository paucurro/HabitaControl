<?php

use App\Models\Comunidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('relaciona una comunidad con su usuario', function () {
    $user = User::factory()->create();
    $comunidad = Comunidad::factory()->for($user)->create();

    expect($comunidad->user->is($user))->toBeTrue()
        ->and($user->comunidades()->whereKey($comunidad)->exists())->toBeTrue();
});
