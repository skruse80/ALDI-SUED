<?php

namespace App\Health;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

final class ReadinessChecker
{
    /**
     * @return array{status: 'ready'|'not_ready', checks: array<string, array{ok: bool, latency_ms: float}>}
     */
    public function run(): array
    {
        $checks = [
            'database' => $this->timed(fn (): mixed => DB::select('select 1')),
        ];

        if ($this->usesRedis()) {
            $checks['redis'] = $this->timed(function (): void {
                if (! $this->isPong(Redis::connection()->ping())) {
                    throw new \RuntimeException('Redis ping failed.');
                }
            });
        }

        $ready = true;

        foreach ($checks as $check) {
            if (! $check['ok']) {
                $ready = false;
                break;
            }
        }

        return [
            'status' => $ready ? 'ready' : 'not_ready',
            'checks' => $checks,
        ];
    }

    public function usesRedis(): bool
    {
        return in_array('redis', [
            config('cache.default'),
            config('queue.default'),
            config('session.driver'),
        ], true);
    }

    private function isPong(mixed $pong): bool
    {
        if ($pong === true) {
            return true;
        }

        if (is_scalar($pong) || (is_object($pong) && method_exists($pong, '__toString'))) {
            return in_array(strtoupper(ltrim((string) $pong, '+')), ['PONG', '1'], true);
        }

        return false;
    }

    /**
     * @param  callable(): mixed  $check
     * @return array{ok: bool, latency_ms: float}
     */
    private function timed(callable $check): array
    {
        $started = hrtime(true);

        try {
            $check();

            return [
                'ok' => true,
                'latency_ms' => $this->milliseconds($started),
            ];
        } catch (Throwable) {
            return [
                'ok' => false,
                'latency_ms' => $this->milliseconds($started),
            ];
        }
    }

    private function milliseconds(int|float $started): float
    {
        return round((hrtime(true) - $started) / 1_000_000, 1);
    }
}
