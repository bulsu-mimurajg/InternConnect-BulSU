<?php

namespace App\Http\Middleware;

use App\Models\InternshipSeason;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveInternshipSeason
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if there's an active internship season
        $activeSeason = InternshipSeason::getActiveSeason();
        
        if (!$activeSeason) {
            // Only block POST requests (form submissions), allow GET requests (page visits)
            if ($request->isMethod('POST')) {
                return back()->withErrors([
                    'season' => 'Registration is currently closed. No active internship season is available.'
                ])->withInput();
            }
            
            // For GET requests, allow access to the page
            // The controller will handle showing the appropriate message
        }

        return $next($request);
    }
}
