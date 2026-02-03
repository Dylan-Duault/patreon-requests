<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->patron = User::factory()->activePatron()->create();
});

describe('request list visibility setting', function () {
    test('patrons can access request list when setting is enabled', function () {
        Setting::set('show_request_list', true, 'boolean');

        $this->actingAs($this->patron)
            ->get('/requests')
            ->assertOk();
    });

    test('patrons can access request list when setting does not exist (default behavior)', function () {
        $this->actingAs($this->patron)
            ->get('/requests')
            ->assertOk();
    });

    test('patrons cannot access request list when setting is disabled', function () {
        Setting::set('show_request_list', false, 'boolean');

        $this->actingAs($this->patron)
            ->get('/requests')
            ->assertNotFound();
    });

    test('show_request_list setting is shared in inertia props', function () {
        Setting::set('show_request_list', false, 'boolean');

        $this->actingAs($this->patron)
            ->get('/dashboard')
            ->assertInertia(fn ($page) => $page
                ->where('settings.show_request_list', false)
            );
    });

    test('show_request_list setting defaults to true in inertia props', function () {
        $this->actingAs($this->patron)
            ->get('/dashboard')
            ->assertInertia(fn ($page) => $page
                ->where('settings.show_request_list', true)
            );
    });
});
