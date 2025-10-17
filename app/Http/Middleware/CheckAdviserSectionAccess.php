<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdviserSectionAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Only apply to advisers
        if ($user && $user->role === 'adviser') {
            $adviserRecord = $user->adviser;
            
            if ($adviserRecord) {
                // Get all sections assigned to this adviser
                $assignedSections = $adviserRecord->sections;
                
                // Check if any assigned sections are archived
                $archivedSections = $assignedSections->where('status', 'archived');
                
                if ($archivedSections->isNotEmpty()) {
                    // Check if the current request is trying to access an archived section
                    $currentSectionId = $this->getCurrentSectionId($request);
                    
                    if ($currentSectionId && $archivedSections->contains('section_id', $currentSectionId)) {
                        return redirect()->route('adviser.dashboard')
                            ->withErrors(['error' => 'You cannot access archived sections. Please contact an administrator.']);
                    }
                    
                    // Check if trying to switch to an archived section
                    if ($request->routeIs('adviser.switch-section') && $request->route('sectionId')) {
                        $targetSectionId = $request->route('sectionId');
                        
                        if ($archivedSections->contains('section_id', $targetSectionId)) {
                            return redirect()->back()
                                ->withErrors(['error' => 'Cannot switch to archived section. Please contact an administrator.']);
                        }
                    }
                }
            }
        }

        return $next($request);
    }

    /**
     * Get the current section ID from session or request
     */
    private function getCurrentSectionId(Request $request): ?int
    {
        // Check session first
        $sessionSectionId = $request->session()->get('adviser_current_section_id');
        
        if ($sessionSectionId && $sessionSectionId !== 'all') {
            return (int) $sessionSectionId;
        }
        
        // Check route parameters
        if ($request->route('sectionId')) {
            return (int) $request->route('sectionId');
        }
        
        return null;
    }
}