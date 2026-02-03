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

test('recent requests include queue position and completed date', function () {
    $user = User::factory()->activePatron()->create();

    // Create 2 older pending requests from other users
    \App\Models\VideoRequest::factory()->count(2)->pending()->create([
        'requested_at' => now()->subDays(5),
    ]);

    // Create user's pending request (should be position 3)
    $pendingRequest = \App\Models\VideoRequest::factory()->pending()->create([
        'user_id' => $user->id,
        'requested_at' => now()->subDays(3),
    ]);

    $completedAt = now()->subDay();
    // Create user's completed request
    $completedRequest = \App\Models\VideoRequest::factory()->create([
        'user_id' => $user->id,
        'status' => 'done',
        'completed_at' => $completedAt,
    ]);

    $response = $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('recentRequests', 2)
        );

    $requests = $response->viewData('page')['props']['recentRequests'];

    $completedInResponse = collect($requests)->firstWhere('id', $completedRequest->id);
    $pendingInResponse = collect($requests)->firstWhere('id', $pendingRequest->id);

    expect($completedInResponse['queue_position'])->toBeNull()
        ->and($completedInResponse['completed_at'])->not->toBeNull()
        ->and($pendingInResponse['queue_position'])->toBe(3)
        ->and($pendingInResponse['completed_at'])->toBeNull();
});
