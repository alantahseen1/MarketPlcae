<?php

namespace App\Http\Middleware;

use App\Services\VisitStatsService;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackVisit
{
    public function __construct(
        protected VisitStatsService $visitStatsService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 1. Only count successful GET responses with HTML status 200
        if (! $request->isMethod('GET') || $response->getStatusCode() !== 200) {
            return $response;
        }

        // 2. Exclude staff routes
        if ($request->is('staff*')) {
            return $response;
        }

        // 3. Exclude AJAX, JSON, XML, robots
        if ($request->ajax() || $request->wantsJson() || $request->is('sitemap.xml', 'robots.txt')) {
            return $response;
        }

        // 4. Exclude logged-in staff
        if (Auth::guard('staff')->check()) {
            return $response;
        }

        // 5. Exclude bots and crawlers
        $userAgent = (string) $request->userAgent();
        if ($this->visitStatsService->isBot($userAgent)) {
            return $response;
        }

        // 6. Record visit safely
        try {
            $today = Carbon::today()->toDateString();
            $ip = (string) $request->ip();
            $visitorHash = $this->visitStatsService->makeVisitorHash($ip, $userAgent, $today);

            $this->visitStatsService->record($visitorHash, $today);
        } catch (\Throwable) {
            // Never break user response on tracking failure
        }

        return $response;
    }
}

