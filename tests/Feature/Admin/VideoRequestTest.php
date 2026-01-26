<?php

use App\Models\User;
use App\Models\VideoRequest;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->user = User::factory()->create();
});

describe('rating video requests', function () {
    test('admin can rate a request with thumbs up', function () {
        $videoRequest = VideoRequest::factory()->pending()->create();

        $this->actingAs($this->admin)
            ->patch("/admin/requests/{$videoRequest->id}/rate", ['rating' => 'up'])
            ->assertRedirect();

        $videoRequest->refresh();

        expect($videoRequest->rating)->toBe('up')
            ->and($videoRequest->status)->toBe('done')
            ->and($videoRequest->completed_at)->not->toBeNull();
    });

    test('admin can rate a request with thumbs down', function () {
        $videoRequest = VideoRequest::factory()->pending()->create();

        $this->actingAs($this->admin)
            ->patch("/admin/requests/{$videoRequest->id}/rate", ['rating' => 'down'])
            ->assertRedirect();

        $videoRequest->refresh();

        expect($videoRequest->rating)->toBe('down')
            ->and($videoRequest->status)->toBe('done')
            ->and($videoRequest->completed_at)->not->toBeNull();
    });

    test('rating sets completed_at if not already set', function () {
        $videoRequest = VideoRequest::factory()->pending()->create();

        expect($videoRequest->completed_at)->toBeNull();

        $this->actingAs($this->admin)
            ->patch("/admin/requests/{$videoRequest->id}/rate", ['rating' => 'up']);

        $videoRequest->refresh();

        expect($videoRequest->completed_at)->not->toBeNull();
    });

    test('rating preserves existing completed_at', function () {
        $completedAt = now()->subDay();
        $videoRequest = VideoRequest::factory()->create([
            'status' => 'done',
            'completed_at' => $completedAt,
        ]);

        $this->actingAs($this->admin)
            ->patch("/admin/requests/{$videoRequest->id}/rate", ['rating' => 'up']);

        $videoRequest->refresh();

        expect($videoRequest->completed_at->toDateTimeString())
            ->toBe($completedAt->toDateTimeString());
    });

    test('invalid rating value is rejected', function () {
        $videoRequest = VideoRequest::factory()->pending()->create();

        $this->actingAs($this->admin)
            ->patch("/admin/requests/{$videoRequest->id}/rate", ['rating' => 'invalid'])
            ->assertSessionHasErrors('rating');

        $videoRequest->refresh();

        expect($videoRequest->rating)->toBeNull()
            ->and($videoRequest->status)->toBe('pending');
    });

    test('rating is required', function () {
        $videoRequest = VideoRequest::factory()->pending()->create();

        $this->actingAs($this->admin)
            ->patch("/admin/requests/{$videoRequest->id}/rate", [])
            ->assertSessionHasErrors('rating');
    });

    test('non-admin cannot rate requests', function () {
        $videoRequest = VideoRequest::factory()->pending()->create();

        $this->actingAs($this->user)
            ->patch("/admin/requests/{$videoRequest->id}/rate", ['rating' => 'up'])
            ->assertForbidden();

        $videoRequest->refresh();

        expect($videoRequest->rating)->toBeNull()
            ->and($videoRequest->status)->toBe('pending');
    });

    test('guest cannot rate requests', function () {
        $videoRequest = VideoRequest::factory()->pending()->create();

        $this->patch("/admin/requests/{$videoRequest->id}/rate", ['rating' => 'up'])
            ->assertRedirect(route('login'));
    });
});

describe('reverting to pending', function () {
    test('admin can revert a rated request to pending', function () {
        $videoRequest = VideoRequest::factory()->create([
            'status' => 'done',
            'rating' => 'up',
            'completed_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->patch("/admin/requests/{$videoRequest->id}/pending")
            ->assertRedirect();

        $videoRequest->refresh();

        expect($videoRequest->status)->toBe('pending')
            ->and($videoRequest->rating)->toBeNull()
            ->and($videoRequest->completed_at)->toBeNull();
    });

    test('non-admin cannot revert requests', function () {
        $videoRequest = VideoRequest::factory()->create([
            'status' => 'done',
            'rating' => 'up',
            'completed_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->patch("/admin/requests/{$videoRequest->id}/pending")
            ->assertForbidden();

        $videoRequest->refresh();

        expect($videoRequest->status)->toBe('done');
    });
});

describe('listing video requests', function () {
    test('admin can view requests list', function () {
        VideoRequest::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get('/admin/requests')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/Requests')
                ->has('requests', 3)
            );
    });

    test('requests include rating in response', function () {
        VideoRequest::factory()->create(['rating' => 'up', 'status' => 'done']);
        VideoRequest::factory()->create(['rating' => 'down', 'status' => 'done']);
        VideoRequest::factory()->create(['rating' => null, 'status' => 'pending']);

        $this->actingAs($this->admin)
            ->get('/admin/requests?status=all')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/Requests')
                ->has('requests', 3)
                ->where('requests.0.rating', 'up')
                ->where('requests.1.rating', 'down')
                ->where('requests.2.rating', null)
            );
    });

    test('non-admin cannot view requests list', function () {
        $this->actingAs($this->user)
            ->get('/admin/requests')
            ->assertForbidden();
    });
});
