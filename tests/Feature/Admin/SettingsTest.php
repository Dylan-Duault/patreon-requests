<?php

use App\Models\Setting;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->user = User::factory()->create();
    Setting::query()->delete();
});

describe('admin settings page', function () {
    test('admin can view settings page', function () {
        $this->actingAs($this->admin)
            ->get('/admin/settings')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/Settings')
                ->has('settings')
            );
    });

    test('non-admin cannot view settings page', function () {
        $this->actingAs($this->user)
            ->get('/admin/settings')
            ->assertForbidden();
    });

    test('guest cannot view settings page', function () {
        $this->get('/admin/settings')
            ->assertRedirect();
    });

    test('settings page shows default value when setting does not exist', function () {
        $this->actingAs($this->admin)
            ->get('/admin/settings')
            ->assertInertia(fn ($page) => $page
                ->where('settings.show_request_list', true)
            );
    });

    test('settings page shows stored value', function () {
        Setting::set('show_request_list', false, 'boolean');

        $this->actingAs($this->admin)
            ->get('/admin/settings')
            ->assertInertia(fn ($page) => $page
                ->where('settings.show_request_list', false)
            );
    });
});

describe('updating settings', function () {
    test('admin can enable show_request_list setting', function () {
        Setting::set('show_request_list', false, 'boolean');

        $this->actingAs($this->admin)
            ->patch('/admin/settings', [
                'show_request_list' => true,
            ])
            ->assertRedirect();

        expect(Setting::get('show_request_list'))->toBeTrue();
    });

    test('admin can disable show_request_list setting', function () {
        Setting::set('show_request_list', true, 'boolean');

        $this->actingAs($this->admin)
            ->patch('/admin/settings', [
                'show_request_list' => false,
            ])
            ->assertRedirect();

        expect(Setting::get('show_request_list'))->toBeFalse();
    });

    test('missing show_request_list defaults to false', function () {
        $this->actingAs($this->admin)
            ->patch('/admin/settings', [])
            ->assertRedirect();

        expect(Setting::get('show_request_list'))->toBeFalse();
    });

    test('non-admin cannot update settings', function () {
        $this->actingAs($this->user)
            ->patch('/admin/settings', [
                'show_request_list' => false,
            ])
            ->assertForbidden();

        expect(Setting::get('show_request_list', true))->toBeTrue();
    });

    test('guest cannot update settings', function () {
        $this->patch('/admin/settings', [
            'show_request_list' => false,
        ])
            ->assertRedirect();
    });

    test('invalid boolean value is rejected', function () {
        $this->actingAs($this->admin)
            ->patch('/admin/settings', [
                'show_request_list' => 'invalid',
            ])
            ->assertSessionHasErrors('show_request_list');
    });
});
