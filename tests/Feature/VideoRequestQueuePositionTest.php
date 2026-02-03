<?php

use App\Models\VideoRequest;

describe('queue position', function () {
    test('returns null for completed requests', function () {
        $request = VideoRequest::factory()->create([
            'status' => 'done',
            'completed_at' => now(),
            'requested_at' => now()->subDays(5),
        ]);

        expect($request->getQueuePosition())->toBeNull();
    });

    test('returns 1 for the oldest pending request', function () {
        // Create some older completed requests
        VideoRequest::factory()->count(3)->create([
            'status' => 'done',
            'requested_at' => now()->subDays(10),
        ]);

        // Create the oldest pending request
        $oldestPending = VideoRequest::factory()->pending()->create([
            'requested_at' => now()->subDays(5),
        ]);

        // Create newer pending requests
        VideoRequest::factory()->count(2)->pending()->create([
            'requested_at' => now()->subDays(3),
        ]);

        expect($oldestPending->getQueuePosition())->toBe(1);
    });

    test('returns correct position for middle request', function () {
        // Create 3 older pending requests
        VideoRequest::factory()->count(3)->pending()->create([
            'requested_at' => now()->subDays(5),
        ]);

        // Create the request we want to test (should be position 4)
        $middleRequest = VideoRequest::factory()->pending()->create([
            'requested_at' => now()->subDays(3),
        ]);

        // Create 2 newer pending requests
        VideoRequest::factory()->count(2)->pending()->create([
            'requested_at' => now()->subDay(),
        ]);

        expect($middleRequest->getQueuePosition())->toBe(4);
    });

    test('returns correct position for newest request', function () {
        // Create 4 older pending requests
        VideoRequest::factory()->count(4)->pending()->create([
            'requested_at' => now()->subDays(5),
        ]);

        // Create the newest request
        $newestRequest = VideoRequest::factory()->pending()->create([
            'requested_at' => now(),
        ]);

        expect($newestRequest->getQueuePosition())->toBe(5);
    });

    test('position only counts pending requests', function () {
        // Create many completed requests
        VideoRequest::factory()->count(10)->create([
            'status' => 'done',
            'requested_at' => now()->subDays(10),
        ]);

        // Create first pending request
        $firstPending = VideoRequest::factory()->pending()->create([
            'requested_at' => now()->subDays(3),
        ]);

        // Should be position 1 even though there are 10 completed requests before it
        expect($firstPending->getQueuePosition())->toBe(1);
    });

    test('handles requests with same timestamp correctly', function () {
        $timestamp = now()->subDays(5);

        // Create multiple requests with the same timestamp
        $request1 = VideoRequest::factory()->pending()->create([
            'requested_at' => $timestamp,
        ]);

        $request2 = VideoRequest::factory()->pending()->create([
            'requested_at' => $timestamp,
        ]);

        $request3 = VideoRequest::factory()->pending()->create([
            'requested_at' => $timestamp,
        ]);

        // All should have position 1 since they have the same timestamp
        // (This is expected behavior - if you need different behavior,
        // you could use microseconds or add a secondary sort by ID)
        expect($request1->getQueuePosition())->toBe(1)
            ->and($request2->getQueuePosition())->toBe(1)
            ->and($request3->getQueuePosition())->toBe(1);
    });

    test('position updates when older requests are completed', function () {
        // Create 3 older pending requests
        $older1 = VideoRequest::factory()->pending()->create([
            'requested_at' => now()->subDays(7),
        ]);
        $older2 = VideoRequest::factory()->pending()->create([
            'requested_at' => now()->subDays(6),
        ]);
        $older3 = VideoRequest::factory()->pending()->create([
            'requested_at' => now()->subDays(5),
        ]);

        // Create our test request (should be position 4)
        $testRequest = VideoRequest::factory()->pending()->create([
            'requested_at' => now()->subDays(3),
        ]);

        expect($testRequest->getQueuePosition())->toBe(4);

        // Complete the oldest request
        $older1->update(['status' => 'done', 'completed_at' => now()]);

        // Refresh and check position (should now be 3)
        $testRequest->refresh();
        expect($testRequest->getQueuePosition())->toBe(3);

        // Complete another older request
        $older2->update(['status' => 'done', 'completed_at' => now()]);

        // Refresh and check position (should now be 2)
        $testRequest->refresh();
        expect($testRequest->getQueuePosition())->toBe(2);
    });
});
