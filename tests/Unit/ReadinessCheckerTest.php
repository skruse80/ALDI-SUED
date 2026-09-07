<?php

use App\Health\ReadinessChecker;
use Tests\TestCase;

uses(TestCase::class);

it('reports the database as ready in the test environment', function () {
    $report = app(ReadinessChecker::class)->run();

    expect($report['status'])->toBe('ready')
        ->and($report['checks'])->toHaveKey('database')
        ->and($report['checks']['database']['ok'])->toBeTrue()
        ->and($report['checks'])->not->toHaveKey('redis');
});

it('treats redis as a dependency only when it is configured', function () {
    expect(app(ReadinessChecker::class)->usesRedis())->toBeFalse();

    config([
        'cache.default' => 'redis',
        'queue.default' => 'sync',
        'session.driver' => 'array',
    ]);

    expect(app(ReadinessChecker::class)->usesRedis())->toBeTrue();
});
