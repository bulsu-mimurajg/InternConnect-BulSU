<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\AdditionalInfo;
use App\Services\DeadlineStatusService;
use Inertia\Inertia;
use Inertia\Response;

class AdditionalInfoController extends Controller
{
    /**
     * Display the additional info management page
     */
    public function index(Request $request): Response
    {
        $query = AdditionalInfo::query();

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where('info_name', 'like', "%{$searchTerm}%");
        }

        // Apply default sorting by creation date
        $query->orderBy('created_at', 'desc');

        $additionalInfos = $query->get();

        // Get deadline status for restrictions
        $deadlineStatusService = new DeadlineStatusService();
        $deadlineStatus = $deadlineStatusService->getAdminDeadlineStatus();

        return Inertia::render('admin/additional-info', [
            'additionalInfos' => $additionalInfos,
            'filters' => [
                'search' => $request->get('search', ''),
            ],
            'deadlineStatus' => $deadlineStatus,
        ]);
    }

    /**
     * Store a newly created additional info
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'info_name' => 'required|string|max:255|unique:additional_infos,info_name',
        ]);

        AdditionalInfo::create([
            'info_name' => $request->info_name,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Additional info created successfully!');
    }

    /**
     * Update the specified additional info
     */
    public function update(Request $request, AdditionalInfo $additionalInfo): RedirectResponse
    {
        $request->validate([
            'info_name' => 'required|string|max:255|unique:additional_infos,info_name,' . $additionalInfo->id,
        ]);

        $additionalInfo->update([
            'info_name' => $request->info_name,
        ]);

        return redirect()->back()->with('success', 'Additional info updated successfully!');
    }

    /**
     * Archive the specified additional info
     */
    public function archive(AdditionalInfo $additionalInfo): RedirectResponse
    {
        $additionalInfo->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Additional info archived successfully!');
    }

    /**
     * Restore the specified additional info
     */
    public function restore(AdditionalInfo $additionalInfo): RedirectResponse
    {
        $additionalInfo->update(['is_active' => true]);

        return redirect()->back()->with('success', 'Additional info restored successfully!');
    }

    /**
     * Get active additional infos for student form
     */
    public function getActive()
    {
        return AdditionalInfo::where('is_active', true)
            ->orderBy('info_name')
            ->get();
    }
}
