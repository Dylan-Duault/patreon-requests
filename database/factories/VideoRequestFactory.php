<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VideoRequest>
 */
class VideoRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $videoId = '2QX4W-UvVWE';

        return [
            'user_id' => User::factory(),
            'youtube_url' => "https://www.youtube.com/watch?v={$videoId}",
            'youtube_video_id' => $videoId,
            'title' => fake()->sentence(4),
            'thumbnail' => "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg",
            'status' => 'pending',
            'requested_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'completed_at' => null,
        ];
    }

    /**
     * Indicate that the request is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'done',
            'completed_at' => fake()->dateTimeBetween($attributes['requested_at'], 'now'),
        ]);
    }

    /**
     * Indicate that the request is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'completed_at' => null,
        ]);
    }
}
