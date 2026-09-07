<?php

namespace App\Http\Controllers;

use App\Health\ReadinessChecker;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ShowHomeController extends Controller
{
    public function __invoke(Request $request, ReadinessChecker $readiness): View
    {
        $locale = $request->query('lang') === 'en' ? 'en' : 'de';
        app()->setLocale($locale);

        return view('home', [
            'locale' => $locale,
            'readiness' => $readiness->run(),
        ]);
    }
}
