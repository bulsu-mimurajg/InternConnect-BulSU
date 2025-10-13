<?php

namespace App\Http\Controllers;

use App\Models\InternshipSeason;
use App\Services\InternshipSeasonService;
use App\Services\StudentArchiveService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InternshipSeasonController extends Controller
{
    public function __construct(
        private InternshipSeasonService $seasonService,
        private StudentArchiveService $archiveService
    ) {}

    /**
     * Display all seasons with deadline counts
     */
    public function index(): Response
    {
        $seasons = $this->seasonService->getAllSeasonsWithStats();
        $activeSeason = $this->seasonService->getActiveSeason();

        return Inertia::render('admin/seasons', [
            'seasons' => $seasons,
            'activeSeason' => $activeSeason,
        ]);
    }

    /**
     * Store a new season
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $season = $this->seasonService->createSeason(
                $validated['name'],
                Carbon::parse($validated['start_date']),
                Carbon::parse($validated['end_date'])
            );

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($season)
                ->withProperties([
                    'season_id' => $season->id,
                    'name' => $season->name,
                ])
                ->log('created internship season');

            return redirect()->route('admin.seasons')
                ->with('success', 'Internship season created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create internship season', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to create season: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Update season details
     */
    public function update(Request $request, InternshipSeason $season): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $season->update([
                'name' => $validated['name'],
                'start_date' => Carbon::parse($validated['start_date']),
                'end_date' => Carbon::parse($validated['end_date']),
            ]);

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($season)
                ->withProperties([
                    'season_id' => $season->id,
                    'name' => $season->name,
                ])
                ->log('updated internship season');

            return redirect()->route('admin.seasons')
                ->with('success', 'Internship season updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update internship season', [
                'error' => $e->getMessage(),
                'season_id' => $season->id,
                'request_data' => $request->all(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to update season: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Set season as active
     */
    public function activate(InternshipSeason $season): RedirectResponse
    {
        try {
            $this->seasonService->activateSeason($season->id);

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($season)
                ->withProperties([
                    'season_id' => $season->id,
                    'name' => $season->name,
                ])
                ->log('activated internship season');

            return redirect()->route('admin.seasons')
                ->with('success', 'Season activated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to activate internship season', [
                'error' => $e->getMessage(),
                'season_id' => $season->id,
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to activate season: ' . $e->getMessage()]);
        }
    }

    /**
     * Mark season as completed
     */
    public function complete(InternshipSeason $season): RedirectResponse
    {
        try {
            $this->seasonService->completeSeason($season->id);

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($season)
                ->withProperties([
                    'season_id' => $season->id,
                    'name' => $season->name,
                ])
                ->log('completed internship season');

            return redirect()->route('admin.seasons')
                ->with('success', 'Season completed successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to complete internship season', [
                'error' => $e->getMessage(),
                'season_id' => $season->id,
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to complete season: ' . $e->getMessage()]);
        }
    }

    /**
     * Trigger student archiving for season
     */
    public function archiveStudents(InternshipSeason $season): RedirectResponse
    {
        try {
            // Check if season can be archived
            $canArchive = $this->seasonService->canArchiveSeason($season->id);
            
            if (!$canArchive['can_archive']) {
                return redirect()->back()
                    ->withErrors(['error' => $canArchive['reason']]);
            }

            $archivedCount = $this->archiveService->archiveStudentsBySeason($season->id);

            // Complete the season after archiving
            $this->seasonService->completeSeason($season->id);

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($season)
                ->withProperties([
                    'season_id' => $season->id,
                    'name' => $season->name,
                    'archived_count' => $archivedCount,
                ])
                ->log('archived students for internship season');

            return redirect()->route('admin.seasons')
                ->with('success', "Successfully archived {$archivedCount} students and completed the season.");
        } catch (\Exception $e) {
            Log::error('Failed to archive students for season', [
                'error' => $e->getMessage(),
                'season_id' => $season->id,
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to archive students: ' . $e->getMessage()]);
        }
    }

    /**
     * Get season statistics
     */
    public function stats(InternshipSeason $season): Response
    {
        $stats = $this->seasonService->getSeasonStats($season->id);
        $archivedStudents = $this->archiveService->getArchivedStudentsBySeason($season->id);

        return Inertia::render('admin/seasons/stats', [
            'season' => $season,
            'stats' => $stats,
            'archivedStudents' => $archivedStudents,
        ]);
    }

    /**
     * Get archived students for a season
     */
    public function archivedStudents(InternshipSeason $season): Response
    {
        $archivedStudents = $this->archiveService->getArchivedStudentsBySeason($season->id);

        return Inertia::render('admin/seasons/archived-students', [
            'season' => $season,
            'archivedStudents' => $archivedStudents,
        ]);
    }
}