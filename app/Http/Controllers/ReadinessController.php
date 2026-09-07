<?php

namespace App\Http\Controllers;

use App\Health\ReadinessChecker;
use Illuminate\Http\JsonResponse;

final class ReadinessController extends Controller
{
    public function __invoke(ReadinessChecker $readiness): JsonResponse
    {
        $report = $readiness->run();
        $status = $report['status'] === 'ready' ? 200 : 503;

        return response()
            ->json($report, $status)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }
}
