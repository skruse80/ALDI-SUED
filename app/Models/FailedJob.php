<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\FailedJobFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property string $connection
 * @property string $queue
 * @property array<string, mixed> $payload
 * @property string $exception
 * @property CarbonImmutable $failed_at
 */
#[Fillable(['uuid', 'connection', 'queue', 'payload', 'exception', 'failed_at'])]
class FailedJob extends Model
{
    /** @use HasFactory<FailedJobFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'failed_jobs';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'failed_at' => 'immutable_datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function displayName(): string
    {
        $name = $this->payload['displayName'] ?? null;

        if (is_string($name) && $name !== '') {
            return $name;
        }

        $data = $this->payload['data'] ?? null;

        if (is_array($data)) {
            $commandName = $data['commandName'] ?? null;

            if (is_string($commandName) && $commandName !== '') {
                return $commandName;
            }
        }

        return 'Unbekannt';
    }
}
