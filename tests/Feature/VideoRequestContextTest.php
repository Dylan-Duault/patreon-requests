<?php

use App\Models\User;
use App\Models\VideoRequest;

beforeEach(function () {
    $this->patron = User::factory()->activePatron()->create();
    $this->otherPatron = User::factory()->activePatron()->create();
});

describe('updating video request context', function () {
    test('patron can update context of their own pending request', function () {
        $videoRequest = VideoRequest::factory()->pending()->create([
            'user_id' => $this->patron->id,
            'context' => 'Original context',
        ]);

        $this->actingAs($this->patron)
            ->patch("/requests/{$videoRequest->id}/context", [
                'context' => 'Updated context',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Context updated successfully!');

        $videoRequest->refresh();

        expect($videoRequest->context)->toBe('Updated context');
    });

    test('patron can clear context by setting it to null', function () {
        $videoRequest = VideoRequest::factory()->pending()->create([
            'user_id' => $this->patron->id,
            'context' => 'Original context',
        ]);

        $this->actingAs($this->patron)
            ->patch("/requests/{$videoRequest->id}/context", [
                'context' => null,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Context updated successfully!');

        $videoRequest->refresh();

        expect($videoRequest->context)->toBeNull();
    });

    test('patron can clear context by setting it to empty string', function () {
        $videoRequest = VideoRequest::factory()->pending()->create([
            'user_id' => $this->patron->id,
            'context' => 'Original context',
        ]);

        $this->actingAs($this->patron)
            ->patch("/requests/{$videoRequest->id}/context", [
                'context' => '',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Context updated successfully!');

        $videoRequest->refresh();

        expect($videoRequest->context)->toBeNull();
    });

    test('patron cannot update context of another patrons request', function () {
        $videoRequest = VideoRequest::factory()->pending()->create([
            'user_id' => $this->otherPatron->id,
            'context' => 'Original context',
        ]);

        $this->actingAs($this->patron)
            ->patch("/requests/{$videoRequest->id}/context", [
                'context' => 'Trying to update',
            ])
            ->assertForbidden();

        $videoRequest->refresh();

        expect($videoRequest->context)->toBe('Original context');
    });

    test('patron cannot update context of completed request', function () {
        $videoRequest = VideoRequest::factory()->create([
            'user_id' => $this->patron->id,
            'status' => 'done',
            'context' => 'Original context',
            'completed_at' => now(),
        ]);

        $this->actingAs($this->patron)
            ->patch("/requests/{$videoRequest->id}/context", [
                'context' => 'Trying to update',
            ])
            ->assertForbidden();

        $videoRequest->refresh();

        expect($videoRequest->context)->toBe('Original context');
    });

    test('context cannot exceed 500 characters', function () {
        $videoRequest = VideoRequest::factory()->pending()->create([
            'user_id' => $this->patron->id,
            'context' => 'Original context',
        ]);

        $longContext = str_repeat('a', 501);

        $this->actingAs($this->patron)
            ->patch("/requests/{$videoRequest->id}/context", [
                'context' => $longContext,
            ])
            ->assertSessionHasErrors('context');

        $videoRequest->refresh();

        expect($videoRequest->context)->toBe('Original context');
    });

    test('context can be exactly 500 characters', function () {
        $videoRequest = VideoRequest::factory()->pending()->create([
            'user_id' => $this->patron->id,
            'context' => 'Original context',
        ]);

        $maxContext = str_repeat('a', 500);

        $this->actingAs($this->patron)
            ->patch("/requests/{$videoRequest->id}/context", [
                'context' => $maxContext,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Context updated successfully!');

        $videoRequest->refresh();

        expect($videoRequest->context)->toBe($maxContext);
    });

    test('guest cannot update context', function () {
        $videoRequest = VideoRequest::factory()->pending()->create([
            'user_id' => $this->patron->id,
            'context' => 'Original context',
        ]);

        $this->patch("/requests/{$videoRequest->id}/context", [
            'context' => 'Trying to update',
        ])
            ->assertRedirect();

        $videoRequest->refresh();

        expect($videoRequest->context)->toBe('Original context');
    });

    test('non-patron user cannot update context', function () {
        $nonPatron = User::factory()->formerPatron()->create();

        $videoRequest = VideoRequest::factory()->pending()->create([
            'user_id' => $nonPatron->id,
            'context' => 'Original context',
        ]);

        $this->actingAs($nonPatron)
            ->patch("/requests/{$videoRequest->id}/context", [
                'context' => 'Trying to update',
            ])
            ->assertRedirect(route('subscribe'));

        $videoRequest->refresh();

        expect($videoRequest->context)->toBe('Original context');
    });
});
