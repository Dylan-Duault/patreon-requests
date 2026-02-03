<?php

use App\Models\Setting;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard includes showRequestList setting when enabled', function () {
    Setting::set('show_request_list', true);
    $user = User::factory()->activePatron()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('showRequestList', true)
        );
});

test('dashboard includes showRequestList setting when disabled', function () {
    Setting::set('show_request_list', false);
    $user = User::factory()->activePatron()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('showRequestList', false)
        );
});

test('dashboard defaults showRequestList to true when setting does not exist', function () {
    $user = User::factory()->activePatron()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('showRequestList', true)
        );
});
