<?php

use App\Filament\Resources\FailedJobs\Pages\ListFailedJobs;
use App\Filament\Resources\FailedJobs\Pages\ViewFailedJob;
use App\Jobs\ProcessPriceUpdateJob;
use App\Models\FailedJob;
use App\Models\User;
use App\Policies\FailedJobPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('lists failed jobs in the admin panel', function () {
    $this->actingAs(User::factory()->create());
    FailedJob::factory()->create([
        'exception' => 'ModelNotFoundException: product missing',
    ]);

    Livewire::test(ListFailedJobs::class)
        ->assertSuccessful()
        ->assertSee(ProcessPriceUpdateJob::class)
        ->assertSee('ModelNotFoundException');
});

it('shows a failed job exception on the view page', function () {
    $this->actingAs(User::factory()->create());
    $failedJob = FailedJob::factory()->create([
        'exception' => 'ModelNotFoundException: product missing',
    ]);

    Livewire::test(ViewFailedJob::class, ['record' => $failedJob->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('ModelNotFoundException: product missing');
});

it('allows discarding failed jobs but not creating them', function () {
    $user = User::factory()->create();
    $failedJob = FailedJob::factory()->create();
    $policy = app(FailedJobPolicy::class);

    expect($policy->view($user, $failedJob))->toBeTrue()
        ->and($policy->create($user))->toBeFalse()
        ->and($policy->update($user, $failedJob))->toBeFalse()
        ->and($policy->delete($user, $failedJob))->toBeTrue();
});
