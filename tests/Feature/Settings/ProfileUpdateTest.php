<?php

use App\Models\User;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get(route('profile.edit'))->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('username', 'test_user')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
    expect($user->username)->toEqual('test_user');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->set('username', $user->username)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('username must be unique', function () {
    $existing = User::factory()->create(['username' => 'takenname']);
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', $user->name)
        ->set('email', $user->email)
        ->set('username', 'takenname')
        ->call('updateProfileInformation');

    $response->assertHasErrors(['username']);
});

test('username can only contain letters, numbers and underscores', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', $user->name)
        ->set('email', $user->email)
        ->set('username', 'invalid username!')
        ->call('updateProfileInformation');

    $response->assertHasErrors(['username']);
});

test('user can update their own username without uniqueness conflict', function () {
    $user = User::factory()->create(['username' => 'myusername']);

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', $user->name)
        ->set('email', $user->email)
        ->set('username', 'myusername')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect(User::withTrashed()->find($user->id)?->deleted_at)->not->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('deleting account frees up email and username for re-registration', function () {
    $user = User::factory()->create([
        'name' => 'Aleksandra',
        'email' => 'tester@example.com',
    ]);
    $originalUsername = $user->username;

    $this->actingAs($user);

    Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertHasNoErrors();

    $trashed = User::withTrashed()->find($user->id);

    expect($trashed->email)->not->toEqual('tester@example.com');
    expect($trashed->username)->not->toEqual($originalUsername);

    $response = $this->post(route('register.store'), [
        'name' => 'Aleksandra',
        'email' => 'tester@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors();

    expect(User::where('email', 'tester@example.com')->exists())->toBeTrue();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});
