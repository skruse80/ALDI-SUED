<?php

test('the liveness endpoint is available', function () {
    $this->get('/up')->assertOk();
});

test('the readiness endpoint reports configured dependencies', function () {
    $response = $this->getJson('/ready');

    $response
        ->assertOk()
        ->assertJsonPath('status', 'ready')
        ->assertJsonPath('checks.database.ok', true)
        ->assertJsonMissingPath('checks.redis');

    expect((string) $response->headers->get('Cache-Control'))->toContain('no-store');
});
