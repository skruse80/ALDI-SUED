<?php

namespace Database\Factories;

use App\Jobs\ProcessPriceUpdateJob;
use App\Models\FailedJob;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FailedJob>
 */
class FailedJobFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'connection' => 'redis',
            'queue' => 'default',
            'payload' => [
                'uuid' => fake()->uuid(),
                'displayName' => ProcessPriceUpdateJob::class,
                'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
                'data' => [
                    'commandName' => ProcessPriceUpdateJob::class,
                ],
            ],
            'exception' => 'Illuminate\\Database\\Eloquent\\ModelNotFoundException: No query results for model [App\\Models\\Product].',
            'failed_at' => now(),
        ];
    }
}
