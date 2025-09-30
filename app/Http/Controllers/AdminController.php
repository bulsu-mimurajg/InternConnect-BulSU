<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\HTE;
use App\Models\Adviser;
use App\Models\Section;
use App\Models\Internship;
use App\Models\StudentMatch;
use App\Models\StudentPlacement;
use App\Models\StudentScore;
use App\Models\Deadline;
use App\Models\Question;
use App\Models\Category;
use App\Services\ChartGeneratorService;
use App\Services\NotificationService;
use App\Services\AutomaticEndorsementService;
use App\Services\AutomaticPlacementService;
use App\Services\CentralizedDeadlineNotificationService;
use App\Notifications\HTECredentialsNotification;
use App\Notifications\AdviserCredentialsNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard with comprehensive statistics.
     */
    public function dashboard(): Response
    {
        // Get comprehensive statistics
        $stats = $this->getDashboardStats();

        // Get recent activity
        $recentActivity = $this->getRecentActivity();

        // Get placement overview
        $placementOverview = $this->getPlacementOverview();

        // Get section statistics
        $sectionStats = $this->getSectionStats();

        // Get HTE statistics
        $hteStats = $this->getHTEStats();

        // Get analytics data for charts
        $placementAnalytics = $this->getPlacementAnalytics();
        $studentAnalytics = $this->getStudentAnalytics();
        $hteAnalytics = $this->getHTEAnalytics();
        $sectionAnalytics = $this->getSectionAnalytics();
        $assessmentTrends = $this->getAssessmentTrends();
        $placementTrends = $this->getPlacementTrends();

        return Inertia::render('admin/dashboard', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'placementOverview' => $placementOverview,
            'sectionStats' => $sectionStats,
            'hteStats' => $hteStats,
            'placementAnalytics' => $placementAnalytics,
            'studentAnalytics' => $studentAnalytics,
            'hteAnalytics' => $hteAnalytics,
            'sectionAnalytics' => $sectionAnalytics,
            'assessmentTrends' => $assessmentTrends,
            'placementTrends' => $placementTrends,
        ]);
    }

    /**
     * Get comprehensive dashboard statistics.
     */
    private function getDashboardStats(): array
    {
        // Total students
        $totalStudents = Student::where('is_active', true)->count();

        // Students who have completed assessment
        $completedAssessments = Student::where('is_active', true)
            ->where('is_submit', true)
            ->count();

        // Students who have been placed
        $placedStudents = StudentPlacement::where('status', 'approved')->count();

        // Total HTEs
        $totalHTEs = HTE::where('is_active', true)->count();

        // Active HTEs (submitted form)
        $activeHTEs = HTE::where('is_active', true)
            ->where('is_submit', true)
            ->count();

        // Total internships
        $totalInternships = Internship::where('is_active', true)->count();

        // Total available slots
        $totalSlots = Internship::where('is_active', true)->sum('slot_count');

        // Pending placements
        $pendingPlacements = StudentPlacement::where('status', 'pending')->count();

        // Calculate rates
        $completionRate = $totalStudents > 0 ? round(($completedAssessments / $totalStudents) * 100, 1) : 0;
        $placementRate = $completedAssessments > 0 ? round(($placedStudents / $completedAssessments) * 100, 1) : 0;
        $hteParticipationRate = $totalHTEs > 0 ? round(($activeHTEs / $totalHTEs) * 100, 1) : 0;

        return [
            'totalStudents' => $totalStudents,
            'completedAssessments' => $completedAssessments,
            'placedStudents' => $placedStudents,
            'totalHTEs' => $totalHTEs,
            'activeHTEs' => $activeHTEs,
            'totalInternships' => $totalInternships,
            'totalSlots' => $totalSlots,
            'pendingPlacements' => $pendingPlacements,
            'completionRate' => $completionRate,
            'placementRate' => $placementRate,
            'hteParticipationRate' => $hteParticipationRate,
        ];
    }

    /**
     * Get recent activity data.
     */
    private function getRecentActivity(): array
    {
        // Recent student registrations
        $recentStudents = Student::with('section')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'student_number' => $student->student_number,
                    'section' => $student->section->section_name ?? 'N/A',
                    'created_at' => $student->created_at->format('M d, Y'),
                    'type' => 'student_registration',
                ];
            });

        // Recent HTE registrations
        $recentHTEs = HTE::with('user')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($hte) {
                return [
                    'id' => $hte->id,
                    'name' => $hte->company_name,
                    'contact_person' => $hte->cperson_fname . ' ' . $hte->cperson_lname,
                    'created_at' => $hte->created_at->format('M d, Y'),
                    'type' => 'hte_registration',
                ];
            });

        // Recent placements
        $recentPlacements = StudentPlacement::with(['student.section', 'internship.hte'])
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($placement) {
                return [
                    'id' => $placement->id,
                    'student_name' => $placement->student->first_name . ' ' . $placement->student->last_name,
                    'company_name' => $placement->internship->hte->company_name,
                    'position' => $placement->internship->position_title,
                    'created_at' => $placement->created_at->format('M d, Y'),
                    'type' => 'placement',
                ];
            });

        // Combine and sort by date
        $allActivity = collect()
            ->merge($recentStudents)
            ->merge($recentHTEs)
            ->merge($recentPlacements)
            ->sortByDesc('created_at')
            ->take(10)
            ->values()
            ->toArray();

        return $allActivity;
    }

    /**
     * Get placement overview data.
     */
    private function getPlacementOverview(): array
    {
        // Placements by status - include both actual placements and matches
        $placementsByStatus = StudentPlacement::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Add matches from student_matches table for all statuses
        $matchesByStatus = StudentMatch::select('placement_status', DB::raw('count(*) as count'))
            ->whereNotNull('placement_status')
            ->groupBy('placement_status')
            ->get()
            ->pluck('count', 'placement_status')
            ->toArray();

        // Combine the counts
        foreach ($matchesByStatus as $status => $count) {
            $placementsByStatus[$status] = ($placementsByStatus[$status] ?? 0) + $count;
        }

        // Placements by company
        $placementsByCompany = StudentPlacement::with('internship.hte')
            ->where('status', 'approved')
            ->get()
            ->groupBy('internship.hte.company_name')
            ->map(function ($placements, $companyName) {
                return [
                    'company' => $companyName,
                    'count' => $placements->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(10)
            ->values()
            ->toArray();

        // Placements by section
        $placementsBySection = StudentPlacement::with('student.section')
            ->where('status', 'approved')
            ->get()
            ->groupBy('student.section.section_name')
            ->map(function ($placements, $sectionName) {
                return [
                    'section' => $sectionName,
                    'count' => $placements->count(),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->toArray();

        return [
            'byStatus' => $placementsByStatus,
            'byCompany' => $placementsByCompany,
            'bySection' => $placementsBySection,
        ];
    }

    /**
     * Get section statistics.
     */
    private function getSectionStats(): array
    {
        return Student::with('section')
            ->where('is_active', true)
            ->get()
            ->groupBy('section.section_name')
            ->map(function ($students, $sectionName) {
                $totalStudents = $students->count();
                $completedAssessments = $students->where('is_submit', true)->count();
                $placedStudents = $students->filter(function ($student) {
                    return $student->placements()->where('status', 'approved')->exists();
                })->count();

                return [
                    'section' => $sectionName,
                    'totalStudents' => $totalStudents,
                    'completedAssessments' => $completedAssessments,
                    'placedStudents' => $placedStudents,
                    'completionRate' => $totalStudents > 0 ? round(($completedAssessments / $totalStudents) * 100, 1) : 0,
                    'placementRate' => $completedAssessments > 0 ? round(($placedStudents / $completedAssessments) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('totalStudents')
            ->values()
            ->toArray();
    }

    /**
     * Get HTE statistics.
     */
    private function getHTEStats(): array
    {
        return HTE::with(['internships', 'user'])
            ->where('is_active', true)
            ->get()
            ->map(function ($hte) {
                $totalInternships = $hte->internships->count();
                $activeInternships = $hte->internships->where('is_active', true)->count();
                $totalSlots = $hte->internships->sum('slot_count');
                
                // Calculate filled slots from placements
                $filledSlots = StudentPlacement::whereHas('internship', function ($query) use ($hte) {
                    $query->where('hte_id', $hte->id);
                })->where('status', 'approved')->count();
                
                $utilizationRate = $totalSlots > 0 ? round(($filledSlots / $totalSlots) * 100, 1) : 0;

                return [
                    'id' => $hte->id,
                    'company_name' => $hte->company_name,
                    'contact_person' => $hte->cperson_fname . ' ' . $hte->cperson_lname,
                    'email' => $hte->company_email,
                    'is_submit' => $hte->is_submit,
                    'totalInternships' => $totalInternships,
                    'activeInternships' => $activeInternships,
                    'totalSlots' => $totalSlots,
                    'filledSlots' => $filledSlots,
                    'utilizationRate' => $utilizationRate,
                    'created_at' => $hte->created_at->format('M d, Y'),
                ];
            })
            ->sortByDesc('utilizationRate')
            ->take(10)
            ->values()
            ->toArray();
    }

    /**
     * Display activity logs page
     */
    public function logs(Request $request): Response
    {
        $query = Activity::with(['causer', 'subject']);

        // Apply filters
        if ($request->filled('type')) {
            $type = $request->type;
            if ($type === 'login') {
                $query->where('description', 'like', '%logged in%');
            } elseif ($type === 'logout') {
                $query->where('description', 'like', '%logged out%');
            } elseif ($type === 'created') {
                $query->where('description', 'like', '%created%');
            } elseif ($type === 'updated') {
                $query->where('description', 'like', '%updated%');
            } elseif ($type === 'archived') {
                $query->where('description', 'like', '%archived%');
            } elseif ($type === 'deleted') {
                $query->where('description', 'like', '%deleted%');
            }
        }

        if ($request->filled('user')) {
            $query->whereHas('causer', function ($q) use ($request) {
                $q->where('username', 'like', "%{$request->user}%")
                  ->orWhere('email', 'like', "%{$request->user}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('causer.roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->latest()
            ->paginate(20)
            ->through(function ($activity) {
                // Get user role from properties or causer
                $userRole = 'System';
                if ($activity->causer) {
                    $userRole = $activity->causer->getRoleNames()->first() ?? 'No Role';
                } elseif (isset($activity->properties['role'])) {
                    $userRole = $activity->properties['role'];
                }

                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'causer_name' => $activity->causer ? $activity->causer->username : 'System',
                    'causer_email' => $activity->causer ? $activity->causer->email : null,
                    'causer_role' => $userRole,
                    'subject_type' => $activity->subject_type,
                    'subject_id' => $activity->subject_id,
                    'properties' => $activity->properties,
                    'created_at' => $activity->created_at->format('M d, Y H:i:s'),
                    'created_at_human' => $activity->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('admin/logs', [
            'activities' => $activities,
            'filters' => $request->only(['type', 'user', 'role', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Display comprehensive reports page
     */
    public function report(): Response
    {
        // Get all sections for report generation
        $sections = Section::orderBy('section_name')->get();

        return Inertia::render('admin/report', [
            'sections' => $sections,
        ]);
    }

    /**
     * Export section-specific report to PDF
     */
    public function exportSectionPDF(Request $request, $sectionId, $reportType): \Illuminate\Http\Response
    {
        // Validate report type
        $validReportTypes = ['student-list', 'placed-students', 'registered-students', 'assessment-summary', 'performance-analysis'];
        if (!in_array($reportType, $validReportTypes)) {
            abort(404, 'Invalid report type.');
        }

        // Get section
        $section = Section::findOrFail($sectionId);
        
        // Get report data based on type
        $reportData = $this->getSectionReportData($sectionId, $reportType);

        // Generate HTML content for PDF
        $html = view("reports.admin-{$reportType}", array_merge($reportData, [
            'sectionName' => $section->section_name,
            'generatedAt' => now()->format('F d, Y \a\t h:i A'),
        ]))->render();

        // Generate PDF using DomPDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        // Return PDF download
        return $pdf->download("{$reportType}-report-{$section->section_name}-" . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export section-specific report to Excel (CSV format)
     */
    public function exportSectionExcel(Request $request, $sectionId, $reportType): \Illuminate\Http\Response
    {
        // Validate report type
        $validReportTypes = ['student-list', 'placed-students', 'registered-students', 'assessment-summary', 'performance-analysis'];
        if (!in_array($reportType, $validReportTypes)) {
            abort(404, 'Invalid report type.');
        }

        $section = Section::findOrFail($sectionId);
        $csvContent = $this->generateSectionCSVContent($sectionId, $reportType, $section->section_name);

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $reportType . '-report-' . $section->section_name . '-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Export general report to PDF
     */
    public function exportGeneralPDF(Request $request, $reportType): \Illuminate\Http\Response
    {
        // Validate report type
        $validReportTypes = ['comprehensive', 'overview', 'all-students', 'all-placements', 'hte-performance', 'section-comparison'];
        if (!in_array($reportType, $validReportTypes)) {
            abort(404, 'Invalid report type.');
        }

        // Get report data based on type
        $reportData = $this->getGeneralReportData($reportType);

        // Generate HTML content for PDF
        $html = view("reports.admin-general-{$reportType}", array_merge($reportData, [
            'generatedAt' => now()->format('F d, Y \a\t h:i A'),
        ]))->render();

        // Generate PDF using DomPDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        // Return PDF download
        return $pdf->download("{$reportType}-report-" . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export general report to Excel (CSV format)
     */
    public function exportGeneralExcel(Request $request, $reportType): \Illuminate\Http\Response
    {
        // Validate report type
        $validReportTypes = ['comprehensive', 'overview', 'all-students', 'all-placements', 'hte-performance', 'section-comparison'];
        if (!in_array($reportType, $validReportTypes)) {
            abort(404, 'Invalid report type.');
        }

        $csvContent = $this->generateGeneralCSVContent($reportType);

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $reportType . '-report-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Get detailed placement analytics
     */
    private function getPlacementAnalytics(): array
    {
        // Placement success rate by company
        $companyPlacements = StudentPlacement::with('internship.hte')
            ->where('status', 'approved')
            ->get()
            ->groupBy('internship.hte.company_name')
            ->map(function ($placements, $companyName) {
                $totalSlots = $placements->sum('internship.slot_count');
                $filledSlots = $placements->count();
                $successRate = $totalSlots > 0 ? round(($filledSlots / $totalSlots) * 100, 1) : 0;

                return [
                    'company' => $companyName,
                    'totalSlots' => $totalSlots,
                    'filledSlots' => $filledSlots,
                    'successRate' => $successRate,
                ];
            })
            ->sortByDesc('successRate')
            ->take(15)
            ->values()
            ->toArray();

        // Placement by department
        $departmentPlacements = StudentPlacement::with('internship')
            ->where('status', 'approved')
            ->get()
            ->groupBy('internship.department')
            ->map(function ($placements, $department) {
                return [
                    'department' => $department,
                    'count' => $placements->count(),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->toArray();

        // Average compatibility scores
        $avgCompatibilityScores = StudentPlacement::where('status', 'approved')
            ->select(DB::raw('AVG(compatibility_score) as avg_score'))
            ->first();

        return [
            'companyPlacements' => $companyPlacements,
            'departmentPlacements' => $departmentPlacements,
            'avgCompatibilityScore' => round($avgCompatibilityScores->avg_score ?? 0, 2),
        ];
    }

    /**
     * Get student performance analytics
     */
    private function getStudentAnalytics(): array
    {
        // Students by assessment completion status
        $assessmentStatus = Student::where('is_active', true)
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN is_submit = 1 THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN is_submit = 0 THEN 1 ELSE 0 END) as pending')
            )
            ->first();

        // Average scores by category
        $categoryScores = Category::with(['subCategories.studentScores'])
            ->get()
            ->map(function ($category) {
                $totalScore = 0;
                $totalCount = 0;

                foreach ($category->subCategories as $subcategory) {
                    foreach ($subcategory->studentScores as $score) {
                        $totalScore += $score->score;
                        $totalCount++;
                    }
                }

                $avgScore = $totalCount > 0 ? round($totalScore / $totalCount, 2) : 0;

                return [
                    'category' => $category->category_name,
                    'avgScore' => $avgScore,
                    'totalAssessments' => $totalCount,
                ];
            })
            ->sortByDesc('avgScore')
            ->values()
            ->toArray();

        // Top performing students
        $topStudents = Student::with(['scores.subcategory.category', 'section'])
            ->where('is_active', true)
            ->where('is_submit', true)
            ->get()
            ->map(function ($student) {
                $totalScore = $student->scores->avg('score') ?? 0;
                return [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'student_number' => $student->student_number,
                    'section' => $student->section->section_name ?? 'N/A',
                    'avgScore' => round($totalScore, 2),
                ];
            })
            ->sortByDesc('avgScore')
            ->take(10)
            ->values()
            ->toArray();

        return [
            'assessmentStatus' => [
                'total' => $assessmentStatus->total,
                'completed' => $assessmentStatus->completed,
                'pending' => $assessmentStatus->pending,
                'completionRate' => $assessmentStatus->total > 0 ? round(($assessmentStatus->completed / $assessmentStatus->total) * 100, 1) : 0,
            ],
            'categoryScores' => $categoryScores,
            'topStudents' => $topStudents,
        ];
    }

    /**
     * Get HTE performance analytics
     */
    private function getHTEAnalytics(): array
    {
        // HTE participation and performance
        $htePerformance = HTE::with(['internships', 'user'])
            ->where('is_active', true)
            ->get()
            ->map(function ($hte) {
                $totalInternships = $hte->internships->count();
                $activeInternships = $hte->internships->where('is_active', true)->count();
                $totalSlots = $hte->internships->sum('slot_count');
                $filledSlots = StudentPlacement::whereHas('internship', function ($query) use ($hte) {
                    $query->where('hte_id', $hte->id);
                })->where('status', 'approved')->count();

                $utilizationRate = $totalSlots > 0 ? round(($filledSlots / $totalSlots) * 100, 1) : 0;

                return [
                    'id' => $hte->id,
                    'company_name' => $hte->company_name,
                    'contact_person' => $hte->cperson_fname . ' ' . $hte->cperson_lname,
                    'is_submit' => $hte->is_submit,
                    'totalInternships' => $totalInternships,
                    'activeInternships' => $activeInternships,
                    'totalSlots' => $totalSlots,
                    'filledSlots' => $filledSlots,
                    'utilizationRate' => $utilizationRate,
                    'created_at' => $hte->created_at->format('M d, Y'),
                ];
            })
            ->sortByDesc('utilizationRate')
            ->values()
            ->toArray();

        // HTE registration trends
        $registrationTrends = HTE::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();

        return [
            'htePerformance' => $htePerformance,
            'registrationTrends' => $registrationTrends,
        ];
    }

    /**
     * Get section performance analytics
     */
    private function getSectionAnalytics(): array
    {
        return Student::with(['section', 'scores', 'placements'])
            ->where('is_active', true)
            ->get()
            ->groupBy('section.section_name')
            ->map(function ($students, $sectionName) {
                $totalStudents = $students->count();
                $completedAssessments = $students->where('is_submit', true)->count();
                $placedStudents = $students->filter(function ($student) {
                    return $student->placements()->where('status', 'approved')->exists();
                })->count();

                // Calculate average scores for this section
                $totalScore = 0;
                $scoreCount = 0;
                foreach ($students as $student) {
                    $avgStudentScore = $student->scores->avg('score') ?? 0;
                    if ($avgStudentScore > 0) {
                        $totalScore += $avgStudentScore;
                        $scoreCount++;
                    }
                }
                $avgSectionScore = $scoreCount > 0 ? round($totalScore / $scoreCount, 2) : 0;

                return [
                    'section' => $sectionName,
                    'totalStudents' => $totalStudents,
                    'completedAssessments' => $completedAssessments,
                    'placedStudents' => $placedStudents,
                    'completionRate' => $totalStudents > 0 ? round(($completedAssessments / $totalStudents) * 100, 1) : 0,
                    'placementRate' => $completedAssessments > 0 ? round(($placedStudents / $completedAssessments) * 100, 1) : 0,
                    'avgScore' => $avgSectionScore,
                ];
            })
            ->sortByDesc('totalStudents')
            ->values()
            ->toArray();
    }

    /**
     * Get assessment completion trends
     */
    private function getAssessmentTrends(): array
    {
        return Student::select(
                DB::raw('DATE(updated_at) as date'),
                DB::raw('SUM(CASE WHEN is_submit = 1 THEN 1 ELSE 0 END) as completed'),
                DB::raw('COUNT(*) as total')
            )
            ->where('updated_at', '>=', now()->subDays(30))
            ->where('is_active', true)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'completed' => $item->completed,
                    'total' => $item->total,
                    'completionRate' => $item->total > 0 ? round(($item->completed / $item->total) * 100, 1) : 0,
                ];
            })
            ->toArray();
    }

    /**
     * Get placement trends
     */
    private function getPlacementTrends(): array
    {
        return StudentPlacement::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'approved' => $item->approved,
                    'pending' => $item->pending,
                    'rejected' => $item->rejected,
                    'total' => $item->total,
                    'approvalRate' => $item->total > 0 ? round(($item->approved / $item->total) * 100, 1) : 0,
                ];
            })
            ->toArray();
    }

    /**
     * Export reports to PDF
     */
    public function exportPDF(): \Illuminate\Http\Response
    {
        // Get comprehensive statistics
        $stats = $this->getDashboardStats();
        $placementAnalytics = $this->getPlacementAnalytics();
        $studentAnalytics = $this->getStudentAnalytics();
        $hteAnalytics = $this->getHTEAnalytics();
        $sectionAnalytics = $this->getSectionAnalytics();
        $assessmentTrends = $this->getAssessmentTrends();
        $placementTrends = $this->getPlacementTrends();

        // Generate charts for PDF
        $chartGenerator = new ChartGeneratorService();
        $chartData = [
            'stats' => $stats,
            'placementAnalytics' => $placementAnalytics,
            'studentAnalytics' => $studentAnalytics,
            'hteAnalytics' => $hteAnalytics,
            'sectionAnalytics' => $sectionAnalytics,
            'assessmentTrends' => $assessmentTrends,
            'placementTrends' => $placementTrends,
        ];

        $chartImages = $chartGenerator->generateChartsForPDF($chartData);

        // Generate HTML content for PDF with charts
        $html = view('reports.comprehensive', [
            'stats' => $stats,
            'placementAnalytics' => $placementAnalytics,
            'studentAnalytics' => $studentAnalytics,
            'hteAnalytics' => $hteAnalytics,
            'sectionAnalytics' => $sectionAnalytics,
            'assessmentTrends' => $assessmentTrends,
            'placementTrends' => $placementTrends,
            'generatedAt' => now()->format('F d, Y \a\t h:i A'),
            'chartImages' => $chartImages,
        ])->render();

        // Generate PDF using DomPDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        // Clean up temporary chart files
        $chartGenerator->cleanupTempFiles($chartImages);

        // Return PDF download
        return $pdf->download('comprehensive-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export reports to Excel
     */
    public function exportExcel(): \Illuminate\Http\Response
    {
        // Get comprehensive statistics
        $stats = $this->getDashboardStats();
        $placementAnalytics = $this->getPlacementAnalytics();
        $studentAnalytics = $this->getStudentAnalytics();
        $hteAnalytics = $this->getHTEAnalytics();
        $sectionAnalytics = $this->getSectionAnalytics();

        // Create CSV content
        $csvContent = "Comprehensive Report - " . now()->format('F d, Y') . "\n\n";

        // Key Statistics
        $csvContent .= "KEY STATISTICS\n";
        $csvContent .= "Total Students," . $stats['totalStudents'] . "\n";
        $csvContent .= "Completed Assessments," . $stats['completedAssessments'] . "\n";
        $csvContent .= "Placed Students," . $stats['placedStudents'] . "\n";
        $csvContent .= "Total HTEs," . $stats['totalHTEs'] . "\n";
        $csvContent .= "Active HTEs," . $stats['activeHTEs'] . "\n";
        $csvContent .= "Total Internships," . $stats['totalInternships'] . "\n";
        $csvContent .= "Total Slots," . $stats['totalSlots'] . "\n";
        $csvContent .= "Completion Rate," . $stats['completionRate'] . "%\n";
        $csvContent .= "Placement Rate," . $stats['placementRate'] . "%\n";
        $csvContent .= "HTE Participation Rate," . $stats['hteParticipationRate'] . "%\n\n";

        // Top Students
        $csvContent .= "TOP PERFORMING STUDENTS\n";
        $csvContent .= "Rank,Name,Student Number,Section,Average Score\n";
        foreach ($studentAnalytics['topStudents'] as $index => $student) {
            $csvContent .= ($index + 1) . "," . $student['name'] . "," . $student['student_number'] . "," . $student['section'] . "," . $student['avgScore'] . "\n";
        }
        $csvContent .= "\n";

        // Company Placements
        $csvContent .= "COMPANY PLACEMENT PERFORMANCE\n";
        $csvContent .= "Company,Total Slots,Filled Slots,Success Rate\n";
        foreach ($placementAnalytics['companyPlacements'] as $company) {
            $csvContent .= $company['company'] . "," . $company['totalSlots'] . "," . $company['filledSlots'] . "," . $company['successRate'] . "%\n";
        }
        $csvContent .= "\n";

        // Section Analytics
        $csvContent .= "SECTION PERFORMANCE\n";
        $csvContent .= "Section,Total Students,Completed Assessments,Placed Students,Completion Rate,Placement Rate,Average Score\n";
        foreach ($sectionAnalytics as $section) {
            $csvContent .= $section['section'] . "," . $section['totalStudents'] . "," . $section['completedAssessments'] . "," . $section['placedStudents'] . "," . $section['completionRate'] . "%," . $section['placementRate'] . "%," . $section['avgScore'] . "\n";
        }

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="comprehensive-report-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Display HTE management page
     */
    public function hteManagement(): Response
    {
        $htes = HTE::with(['user', 'internships'])
            ->whereHas('user', function($q) {
                $q->where('status', '!=', 'archived');
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($hte) {
                return [
                    'id' => $hte->id,
                    'user_id' => $hte->user_id,
                    'username' => $hte->user->username,
                    'email' => $hte->user->email,
                    'status' => $hte->user->status,
                    'company_name' => $hte->company_name,
                    'company_address' => $hte->company_address,
                    'company_email' => $hte->company_email,
                    'contact_person' => $hte->cperson_fname . ' ' . $hte->cperson_lname,
                    'contact_position' => $hte->cperson_position,
                    'contact_number' => $hte->cperson_contactnum,
                    'is_active' => $hte->is_active,
                    'is_submit' => $hte->is_submit,
                    'created_at' => $hte->created_at->format('M d, Y'),
                    'updated_at' => $hte->updated_at->format('M d, Y'),
                    'internships' => $hte->internships->map(function ($internship) {
                        return [
                            'id' => $internship->id,
                            'position_title' => $internship->position_title,
                            'department' => $internship->department,
                            'placement_description' => $internship->placement_description,
                            'slot_count' => $internship->slot_count,
                            'is_active' => $internship->is_active,
                            'created_at' => $internship->created_at->format('M d, Y'),
                        ];
                    }),
                ];
            });

        // Debug: Log HTE data being sent to frontend
        Log::info('HTE Management Page Data', [
            'total_htes' => $htes->count(),
            'htes_data' => $htes->toArray(),
        ]);

        return Inertia::render('admin/hte-management', [
            'htes' => $htes,
            'showArchived' => false,
        ]);
    }

    /**
     * Display Archived HTEs management page
     */
    public function archivedHTEManagement(): Response
    {
        $htes = HTE::with(['user', 'internships'])
            ->whereHas('user', function ($query) {
                $query->where('status', 'archived');
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($hte) {
                return [
                    'id' => $hte->id,
                    'user_id' => $hte->user_id,
                    'username' => $hte->user->username,
                    'email' => $hte->user->email,
                    'status' => $hte->user->status,
                    'company_name' => $hte->company_name,
                    'company_address' => $hte->company_address,
                    'company_email' => $hte->company_email,
                    'contact_person' => $hte->cperson_fname . ' ' . $hte->cperson_lname,
                    'contact_position' => $hte->cperson_position,
                    'contact_number' => $hte->cperson_contactnum,
                    'is_active' => $hte->is_active,
                    'is_submit' => $hte->is_submit,
                    'created_at' => $hte->created_at->format('M d, Y'),
                    'updated_at' => $hte->updated_at->format('M d, Y'),
                    'internships' => $hte->internships->map(function ($internship) {
                        return [
                            'id' => $internship->id,
                            'position_title' => $internship->position_title,
                            'department' => $internship->department,
                            'placement_description' => $internship->placement_description,
                            'slot_count' => $internship->slot_count,
                            'is_active' => $internship->is_active,
                            'created_at' => $internship->created_at->format('M d, Y'),
                        ];
                    }),
                ];
            });

        return Inertia::render('admin/hte-management', [
            'htes' => $htes,
            'showArchived' => true,
        ]);
    }



    /**
     * Store a new HTE account
     */
    public function storeHTE(Request $request)
    {
        // Debug: Log incoming request data
        Log::info('HTE Creation Request', [
            'email' => $request->email,
            'username' => $request->username,
            'has_password' => !empty($request->password),
            'password_confirmed' => $request->password === $request->password_confirmation,
        ]);

        try {
            $request->validate([
                'email' => 'required|email|max:100|unique:users,email',
                'username' => 'required|string|max:50|unique:users,username',
                'password' => 'required|string|min:8|confirmed',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('HTE Creation Validation Failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            throw $e;
        }

        try {
            DB::beginTransaction();
            Log::info('Starting HTE creation transaction');

            // Create user with HTE role
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 'verified',
                'email_verified_at' => now(),
            ]);

            Log::info('User created successfully', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'status' => $user->status,
            ]);

            // Assign HTE role
            $user->assignRole('hte');
            Log::info('HTE role assigned to user', [
                'user_id' => $user->id,
                'roles' => $user->getRoleNames()->toArray(),
            ]);

            // Create HTE record with default values
            $hte = HTE::create([
                'user_id' => $user->id,
                'company_name' => null, // Will be filled when HTE submits their form
                'company_address' => null,
                'company_email' => null,
                'cperson_fname' => null,
                'cperson_lname' => null,
                'cperson_position' => null,
                'cperson_contactnum' => null,
                'is_active' => true,
                'is_submit' => false,
            ]);

            Log::info('HTE record created successfully', [
                'hte_id' => $hte->id,
                'user_id' => $hte->user_id,
                'is_active' => $hte->is_active,
                'is_submit' => $hte->is_submit,
            ]);

            DB::commit();
            Log::info('HTE creation transaction committed successfully');

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($hte)
                ->withProperties([
                    'hte_id' => $hte->id,
                    'company_name' => $hte->company_name,
                    'email' => $user->email,
                    'username' => $user->username,
                ])
                ->log('created HTE account');

            // Dispatch async notification with credentials
            $user->notify(new HTECredentialsNotification(
                $request->username,
                $request->password,
                $hte->company_name
            ));

            Log::info('HTE credentials notification dispatched', [
                'hte_id' => $hte->id,
                'email' => $user->email,
            ]);

            return redirect()->route('admin.hte')->with('success', 'HTE account created successfully. Credentials have been sent to the provided email address.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('HTE creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            // If it's a validation exception, return the validation errors
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return redirect()->back()->withErrors($e->errors());
            }

            return redirect()->back()->withErrors(['error' => 'Failed to create HTE account: ' . $e->getMessage()]);
        }
    }

    /**
     * Update HTE account
     */
    public function updateHTE(Request $request, HTE $hte)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($hte->user_id),
            ],
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($hte->user_id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'company_name' => 'nullable|string|max:100',
            'company_address' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:100',
            'cperson_fname' => 'nullable|string|max:100',
            'cperson_lname' => 'nullable|string|max:100',
            'cperson_position' => 'nullable|string|max:100',
            'cperson_contactnum' => 'nullable|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            // Capture old values before update
            $oldValues = [
                'email' => $hte->user->email,
                'username' => $hte->user->username,
                'company_name' => $hte->company_name,
                'company_address' => $hte->company_address,
                'company_email' => $hte->company_email,
                'cperson_fname' => $hte->cperson_fname,
                'cperson_lname' => $hte->cperson_lname,
                'cperson_position' => $hte->cperson_position,
                'cperson_contactnum' => $hte->cperson_contactnum,
            ];

            // Update user
            $userData = [
                'email' => $request->email,
                'username' => $request->username,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $hte->user->update($userData);

            // Update HTE
            $hte->update([
                'company_name' => $request->company_name,
                'company_address' => $request->company_address,
                'company_email' => $request->company_email,
                'cperson_fname' => $request->cperson_fname,
                'cperson_lname' => $request->cperson_lname,
                'cperson_position' => $request->cperson_position,
                'cperson_contactnum' => $request->cperson_contactnum,
            ]);

            DB::commit();

            // Capture new values after update
            $newValues = [
                'email' => $request->email,
                'username' => $request->username,
                'company_name' => $request->company_name,
                'company_address' => $request->company_address,
                'company_email' => $request->company_email,
                'cperson_fname' => $request->cperson_fname,
                'cperson_lname' => $request->cperson_lname,
                'cperson_position' => $request->cperson_position,
                'cperson_contactnum' => $request->cperson_contactnum,
            ];

            // Determine which fields actually changed
            $changes = [];
            foreach ($newValues as $key => $newValue) {
                if ($oldValues[$key] !== $newValue) {
                    $changes[$key] = [
                        'old' => $oldValues[$key],
                        'new' => $newValue,
                    ];
                }
            }

            // Log the activity with before/after values
            activity()
                ->causedBy(Auth::user())
                ->performedOn($hte)
                ->withProperties([
                    'hte_id' => $hte->id,
                    'company_name' => $hte->company_name,
                    'changes' => $changes,
                    'password_changed' => $request->filled('password'),
                ])
                ->log('updated HTE account');

            return redirect()->route('admin.hte')->with('success', 'HTE account updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to update HTE account. Please try again.']);
        }
    }

    /**
     * Archive HTE account
     */
    public function archiveHTE(HTE $hte)
    {
        try {
            DB::beginTransaction();

            // Archive the user
            $hte->user->update(['status' => 'archived']);

            // Deactivate the HTE
            $hte->update(['is_active' => false]);

            DB::commit();

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($hte)
                ->withProperties([
                    'hte_id' => $hte->id,
                    'company_name' => $hte->company_name,
                ])
                ->log('archived HTE account');

            return redirect()->route('admin.hte')->with('success', 'HTE account archived successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to archive HTE account. Please try again.']);
        }
    }

    /**
     * Unarchive HTE account
     */
    public function unarchiveHTE(HTE $hte)
    {
        try {
            DB::beginTransaction();

            // Unarchive the user
            $hte->user->update(['status' => 'verified']);

            // Activate the HTE
            $hte->update(['is_active' => true]);

            DB::commit();

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($hte)
                ->withProperties([
                    'hte_id' => $hte->id,
                    'company_name' => $hte->company_name,
                ])
                ->log('unarchived HTE account');

            return redirect()->route('admin.hte.archived')->with('success', 'HTE account unarchived successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to unarchive HTE account. Please try again.']);
        }
    }

    /**
     * Display Adviser management page
     */
    public function adviserManagement(): Response
    {
        $advisers = Adviser::with(['user', 'sections'])
            ->whereHas('user', function ($query) {
                $query->where('status', '!=', 'archived');
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($adviser) {
                return [
                    'id' => $adviser->id,
                    'user_id' => $adviser->user_id,
                    'username' => $adviser->user->username,
                    'email' => $adviser->user->email,
                    'status' => $adviser->user->status,
                    'adviser_fname' => $adviser->adviser_fname,
                    'adviser_lname' => $adviser->adviser_lname,
                    'full_name' => $adviser->adviser_fname . ' ' . $adviser->adviser_lname,
                    'sections' => $adviser->sections->map(function ($section) {
                        return [
                            'section_id' => $section->section_id,
                            'section_name' => $section->section_name,
                        ];
                    }),
                    'section_names' => $adviser->sections->pluck('section_name')->join(', ') ?: 'No Sections',
                    'is_active' => $adviser->is_active,
                    'created_at' => $adviser->created_at->format('M d, Y'),
                    'updated_at' => $adviser->updated_at->format('M d, Y'),
                ];
            });

        // Get all sections for the form
        $sections = Section::orderBy('section_name')->get();

        return Inertia::render('admin/adviser-management', [
            'advisers' => $advisers,
            'sections' => $sections,
            'showArchived' => false,
        ]);
    }

    /**
     * Store a new Adviser account
     */
    public function storeAdviser(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:100|unique:users,email',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:8|confirmed',
            'adviser_fname' => 'required|string|max:100',
            'adviser_lname' => 'required|string|max:100',
            'section_ids' => 'required|array|min:1',
            'section_ids.*' => 'exists:sections,section_id',
        ]);

        try {
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'status' => 'verified',
            ]);

            // Assign adviser role
            $user->assignRole('adviser');

            // Create adviser record
            $adviser = Adviser::create([
                'user_id' => $user->id,
                'adviser_fname' => $request->adviser_fname,
                'adviser_lname' => $request->adviser_lname,
                'is_active' => true,
            ]);

            // Attach sections to adviser
            $adviser->sections()->attach($request->section_ids);

            DB::commit();

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($adviser)
                ->withProperties([
                    'adviser_id' => $adviser->id,
                    'adviser_name' => $adviser->adviser_fname . ' ' . $adviser->adviser_lname,
                    'email' => $user->email,
                    'username' => $user->username,
                    'sections' => Section::whereIn('section_id', $request->section_ids)->pluck('section_name')->toArray(),
                ])
                ->log('created adviser account');

            // Get section names for the notification
            $sectionNames = Section::whereIn('section_id', $request->section_ids)->pluck('section_name')->toArray();
            $adviserName = $request->adviser_fname . ' ' . $request->adviser_lname;

            // Dispatch async notification with credentials
            $user->notify(new AdviserCredentialsNotification(
                $request->username,
                $request->password,
                $adviserName,
                $sectionNames
            ));

            Log::info('Adviser credentials notification dispatched', [
                'adviser_id' => $adviser->id,
                'email' => $user->email,
                'sections' => $sectionNames,
            ]);

            return redirect()->route('admin.adviser')->with('success', 'Adviser account created successfully. Credentials have been sent to the provided email address.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Adviser creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to create adviser account: ' . $e->getMessage()]);
        }
    }

    /**
     * Update Adviser account
     */
    public function updateAdviser(Request $request, Adviser $adviser)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($adviser->user_id),
            ],
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($adviser->user_id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'adviser_fname' => 'required|string|max:100',
            'adviser_lname' => 'required|string|max:100',
            'section_ids' => 'required|array|min:1',
            'section_ids.*' => 'exists:sections,section_id',
        ]);

        try {
            DB::beginTransaction();

            // Capture old values before update
            $oldValues = [
                'email' => $adviser->user->email,
                'username' => $adviser->user->username,
                'adviser_fname' => $adviser->adviser_fname,
                'adviser_lname' => $adviser->adviser_lname,
                'sections' => $adviser->sections->pluck('section_name')->toArray(),
            ];

            // Update user
            $userData = [
                'email' => $request->email,
                'username' => $request->username,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $adviser->user->update($userData);

            // Update adviser
            $adviser->update([
                'adviser_fname' => $request->adviser_fname,
                'adviser_lname' => $request->adviser_lname,
            ]);

            // Sync sections
            $adviser->sections()->sync($request->section_ids);

            DB::commit();

            // Get section names for new section IDs
            $newSectionNames = Section::whereIn('section_id', $request->section_ids)
                ->pluck('section_name')
                ->toArray();

            // Capture new values after update
            $newValues = [
                'email' => $request->email,
                'username' => $request->username,
                'adviser_fname' => $request->adviser_fname,
                'adviser_lname' => $request->adviser_lname,
                'sections' => $newSectionNames,
            ];

            // Determine which fields actually changed
            $changes = [];
            foreach ($newValues as $key => $newValue) {
                if ($oldValues[$key] !== $newValue) {
                    $changes[$key] = [
                        'old' => $oldValues[$key],
                        'new' => $newValue,
                    ];
                }
            }

            // Log the activity with before/after values
            activity()
                ->causedBy(Auth::user())
                ->performedOn($adviser)
                ->withProperties([
                    'adviser_id' => $adviser->id,
                    'adviser_name' => $adviser->adviser_fname . ' ' . $adviser->adviser_lname,
                    'changes' => $changes,
                    'password_changed' => $request->filled('password'),
                ])
                ->log('updated adviser account');

            return redirect()->route('admin.adviser')->with('success', 'Adviser account updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to update adviser account. Please try again.']);
        }
    }

    /**
     * Archive Adviser account
     */
    public function archiveAdviser(Adviser $adviser)
    {
        try {
            DB::beginTransaction();

            // Archive the user
            $adviser->user->update(['status' => 'archived']);

            // Deactivate the adviser
            $adviser->update(['is_active' => false]);

            DB::commit();

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($adviser)
                ->withProperties([
                    'adviser_id' => $adviser->id,
                    'adviser_name' => $adviser->adviser_fname . ' ' . $adviser->adviser_lname,
                ])
                ->log('archived adviser account');

            return redirect()->route('admin.adviser')->with('success', 'Adviser account archived successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to archive adviser account. Please try again.']);
        }
    }

    /**
     * Display Archived Advisers management page
     */
    public function archivedAdviserManagement(): Response
    {
        $advisers = Adviser::with(['user', 'sections'])
            ->whereHas('user', function ($query) {
                $query->where('status', 'archived');
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($adviser) {
                return [
                    'id' => $adviser->id,
                    'user_id' => $adviser->user_id,
                    'username' => $adviser->user->username,
                    'email' => $adviser->user->email,
                    'status' => $adviser->user->status,
                    'adviser_fname' => $adviser->adviser_fname,
                    'adviser_lname' => $adviser->adviser_lname,
                    'full_name' => $adviser->adviser_fname . ' ' . $adviser->adviser_lname,
                    'sections' => $adviser->sections->map(function ($section) {
                        return [
                            'section_id' => $section->section_id,
                            'section_name' => $section->section_name,
                        ];
                    }),
                    'section_names' => $adviser->sections->pluck('section_name')->join(', ') ?: 'No Sections',
                    'is_active' => $adviser->is_active,
                    'created_at' => $adviser->created_at->format('M d, Y'),
                    'updated_at' => $adviser->updated_at->format('M d, Y'),
                ];
            });

        // Get all sections for the form
        $sections = Section::orderBy('section_name')->get();

        return Inertia::render('admin/adviser-management', [
            'advisers' => $advisers,
            'sections' => $sections,
            'showArchived' => true,
        ]);
    }

    /**
     * Unarchive Adviser account
     */
    public function unarchiveAdviser(Adviser $adviser)
    {
        try {
            DB::beginTransaction();

            // Unarchive the user
            $adviser->user->update(['status' => 'verified']);

            // Activate the adviser
            $adviser->update(['is_active' => true]);

            DB::commit();

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($adviser)
                ->withProperties([
                    'adviser_id' => $adviser->id,
                    'adviser_name' => $adviser->adviser_fname . ' ' . $adviser->adviser_lname,
                ])
                ->log('unarchived adviser account');

            return redirect()->route('admin.adviser.archived')->with('success', 'Adviser account unarchived successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to unarchive adviser account. Please try again.']);
        }
    }

    /**
     * Display Section management page
     */
    public function sectionManagement(): Response
    {
        $sections = Section::withCount(['students', 'advisers'])
            ->where('status', 'active')
            ->orderBy('section_name')
            ->get()
            ->map(function ($section) {
                return [
                    'section_id' => $section->section_id,
                    'section_name' => $section->section_name,
                    'status' => $section->status,
                    'student_count' => $section->students_count,
                    'adviser_count' => $section->advisers_count,
                    'created_at' => $section->created_at->format('M d, Y'),
                    'updated_at' => $section->updated_at->format('M d, Y'),
                ];
            });

        return Inertia::render('admin/section', [
            'sections' => $sections,
            'showArchived' => false,
        ]);
    }

    /**
     * Display Archived Sections management page
     */
    public function archivedSectionManagement(): Response
    {
        $sections = Section::withCount(['students', 'advisers'])
            ->where('status', 'archived')
            ->orderBy('section_name')
            ->get()
            ->map(function ($section) {
                return [
                    'section_id' => $section->section_id,
                    'section_name' => $section->section_name,
                    'status' => $section->status,
                    'student_count' => $section->students_count,
                    'adviser_count' => $section->advisers_count,
                    'created_at' => $section->created_at->format('M d, Y'),
                    'updated_at' => $section->updated_at->format('M d, Y'),
                ];
            });

        return Inertia::render('admin/section', [
            'sections' => $sections,
            'showArchived' => true,
        ]);
    }

    /**
     * Store a new Section
     */
    public function storeSection(Request $request)
    {
        $request->validate([
            'section_name' => 'required|string|max:100|unique:sections,section_name',
        ]);

        try {
            DB::beginTransaction();

            $section = Section::create([
                'section_name' => $request->section_name,
                'status' => 'active',
            ]);

            DB::commit();

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($section)
                ->withProperties([
                    'section_id' => $section->section_id,
                    'section_name' => $section->section_name,
                ])
                ->log('created section');

            return redirect()->route('admin.section')->with('success', 'Section created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Section creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to create section: ' . $e->getMessage()]);
        }
    }

    /**
     * Update Section
     */
    public function updateSection(Request $request, Section $section)
    {
        $request->validate([
            'section_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('sections', 'section_name')->ignore($section->section_id, 'section_id'),
            ],
        ]);

        try {
            DB::beginTransaction();

            // Capture old values before update
            $oldValues = [
                'section_name' => $section->section_name,
            ];

            $section->update([
                'section_name' => $request->section_name,
            ]);

            DB::commit();

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($section)
                ->withProperties([
                    'section_id' => $section->section_id,
                    'old_values' => $oldValues,
                    'new_values' => [
                        'section_name' => $section->section_name,
                    ],
                ])
                ->log('updated section');

            return redirect()->route('admin.section')->with('success', 'Section updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to update section. Please try again.']);
        }
    }

    /**
     * Archive Section
     */
    public function archiveSection(Section $section)
    {
        try {
            DB::beginTransaction();

            $section->update(['status' => 'archived']);

            DB::commit();

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($section)
                ->withProperties([
                    'section_id' => $section->section_id,
                    'section_name' => $section->section_name,
                ])
                ->log('archived section');

            return redirect()->route('admin.section')->with('success', 'Section archived successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to archive section. Please try again.']);
        }
    }

    /**
     * Restore Section
     */
    public function restoreSection(Section $section)
    {
        try {
            DB::beginTransaction();

            $section->update(['status' => 'active']);

            DB::commit();

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($section)
                ->withProperties([
                    'section_id' => $section->section_id,
                    'section_name' => $section->section_name,
                ])
                ->log('restored section');

            return redirect()->route('admin.section.archived')->with('success', 'Section restored successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to restore section. Please try again.']);
        }
    }

    /**
     * Display Events management page
     */
    public function eventsManagement(): Response
    {
        // Proactively process deadlines if expired or about to expire (1 minute window)
        try {
            $nowPlusOneMinute = now()->addMinute();

            // Auto-run SIP endorsements when deadline expired or within 1 minute to expiry
            $sipAutoTrigger = \App\Models\Deadline::where('category', 'sip_endorsement')
                ->where(function($query) use ($nowPlusOneMinute) {
                    $query->where('status', 'expired')
                        ->orWhere(function($q) use ($nowPlusOneMinute) {
                            $q->where('status', 'active')
                                ->where('end_date', '<=', $nowPlusOneMinute);
                        });
                })
                ->exists();

            if ($sipAutoTrigger) {
                // Ensure this only runs once per minute
                if (\Illuminate\Support\Facades\Cache::lock('auto-process-sip', 60)->get()) {
                    try {
                        $service = new \App\Services\AutomaticEndorsementService();
                        $service->processSipEndorsements();
                    } finally {
                        \Illuminate\Support\Facades\Cache::lock('auto-process-sip', 60)->release();
                    }
                }
            }

            // Auto-run HTE placements when deadline expired or within 1 minute to expiry
            $hteAutoTrigger = \App\Models\Deadline::where('category', 'student_placements_by_hte')
                ->where(function($query) use ($nowPlusOneMinute) {
                    $query->where('status', 'expired')
                        ->orWhere(function($q) use ($nowPlusOneMinute) {
                            $q->where('status', 'active')
                                ->where('end_date', '<=', $nowPlusOneMinute);
                        });
                })
                ->exists();

            if ($hteAutoTrigger) {
                if (\Illuminate\Support\Facades\Cache::lock('auto-process-hte', 60)->get()) {
                    try {
                        $service = new \App\Services\AutomaticPlacementService();
                        $service->processHtePlacements();
                    } finally {
                        \Illuminate\Support\Facades\Cache::lock('auto-process-hte', 60)->release();
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Auto deadline processing on eventsManagement failed', [
                'error' => $e->getMessage(),
            ]);
        }

        // Get active deadlines
        $activeDeadlines = Deadline::getActive()
            ->map(function ($deadline) {
                return [
                    'id' => $deadline->id,
                    'title' => $deadline->title,
                    'category' => $deadline->category,
                    'category_display' => $deadline->getCategoryDisplayName(),
                    'start_date' => $deadline->start_date->format('Y-m-d\TH:i'),
                    'end_date' => $deadline->end_date->format('Y-m-d\TH:i'),
                    'status' => $deadline->status,
                    'is_active' => $deadline->isActive(),
                    'is_expired' => $deadline->isExpired(),
                    'created_at' => $deadline->created_at->format('M d, Y'),
                    'updated_at' => $deadline->updated_at->format('M d, Y'),
                ];
            });

        // Get expired deadlines
        $expiredDeadlines = Deadline::getExpired()
            ->map(function ($deadline) {
                return [
                    'id' => $deadline->id,
                    'title' => $deadline->title,
                    'category' => $deadline->category,
                    'category_display' => $deadline->getCategoryDisplayName(),
                    'start_date' => $deadline->start_date->format('Y-m-d\TH:i'),
                    'end_date' => $deadline->end_date->format('Y-m-d\TH:i'),
                    'status' => $deadline->status,
                    'is_active' => $deadline->isActive(),
                    'is_expired' => $deadline->isExpired(),
                    'created_at' => $deadline->created_at->format('M d, Y'),
                    'updated_at' => $deadline->updated_at->format('M d, Y'),
                ];
            });

        // Get category options
        $categoryOptions = [
            ['value' => 'student_verification', 'label' => 'Student Verification (Adviser Side)'],
            ['value' => 'student_assessment_form', 'label' => 'Student Assessment Form (Student Side)'],
            ['value' => 'hte_assessment_form', 'label' => 'HTE Assessment Form (HTE Side)'],
            ['value' => 'sip_endorsement', 'label' => 'SIP Endorsement (Admin Side)'],
            ['value' => 'student_placements_by_hte', 'label' => 'Student Placements by HTE (HTE Side)'],
        ];

        return Inertia::render('admin/events', [
            'activeDeadlines' => $activeDeadlines,
            'expiredDeadlines' => $expiredDeadlines,
            'categoryOptions' => $categoryOptions,
        ]);
    }

    /**
     * Store a new deadline
     */
    public function storeDeadline(Request $request)
    {
        // Debug: Log incoming request data
        Log::info('Deadline Creation Request', [
            'title' => $request->title,
            'category' => $request->category,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'all_request_data' => $request->all(),
        ]);

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'category' => 'required|in:student_verification,student_assessment_form,hte_assessment_form,sip_endorsement,student_placements_by_hte',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
            ]);
            Log::info('Deadline validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Deadline Creation Validation Failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            throw $e;
        }

        try {
            // Check if there's already an active deadline for this category
            $existingDeadline = Deadline::where('category', $request->category)
                ->where('status', 'active')
                ->where('end_date', '>', now())
                ->first();

            if ($existingDeadline) {
                return redirect()->back()->withErrors(['error' => 'There is already an active deadline for this category. Please extend or update the existing deadline instead.']);
            }

            $deadline = Deadline::create([
                'title' => $request->title,
                'category' => $request->category,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            Log::info('Deadline created successfully', [
                'deadline_id' => $deadline->id,
                'title' => $deadline->title,
                'category' => $deadline->category,
                'start_date' => $deadline->start_date,
                'end_date' => $deadline->end_date,
                'status' => $deadline->status,
            ]);

            // Send notifications for new deadline
            $notificationService = new NotificationService();
            $notificationService->notifyNewDeadline($deadline);

            // Queue deadline notifications for all users (non-blocking)
            $deadlineService = new CentralizedDeadlineNotificationService();
            $deadlineService->queueDeadlineNotifications();

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($deadline)
                ->withProperties([
                    'deadline_id' => $deadline->id,
                    'title' => $deadline->title,
                    'category' => $deadline->category,
                ])
                ->log('created deadline');

            return redirect()->route('admin.events')->with('success', 'Deadline created successfully.');
        } catch (\Exception $e) {
            Log::error('Deadline creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to create deadline: ' . $e->getMessage()]);
        }
    }

    /**
     * Update a deadline
     */
    public function updateDeadline(Request $request, Deadline $deadline)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:student_verification,student_assessment_form,hte_assessment_form,sip_endorsement,student_placements_by_hte',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            // Check if there's already an active deadline for this category (excluding current deadline)
            if ($request->category !== $deadline->category) {
                $existingDeadline = Deadline::where('category', $request->category)
                    ->where('status', 'active')
                    ->where('end_date', '>', now())
                    ->where('id', '!=', $deadline->id)
                    ->first();

                if ($existingDeadline) {
                    return redirect()->back()->withErrors(['error' => 'There is already an active deadline for this category. Please extend or update the existing deadline instead.']);
                }
            }

            // Capture old values before update
            $oldValues = [
                'title' => $deadline->title,
                'category' => $deadline->category,
                'start_date' => $deadline->start_date,
                'end_date' => $deadline->end_date,
            ];

            $deadline->update([
                'title' => $request->title,
                'category' => $request->category,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            // Queue deadline notifications for this specific deadline (non-blocking)
            $deadlineService = new CentralizedDeadlineNotificationService();
            $deadlineService->queueDeadlineNotificationsForDeadline($deadline);

            // Helper function to compare dates properly
            $compareDates = function($oldDate, $newDate) {
                if ($oldDate === $newDate) return true; // Exact string match
                
                try {
                    $oldDateTime = new \DateTime($oldDate);
                    $newDateTime = new \DateTime($newDate);
                    return $oldDateTime->format('Y-m-d H:i:s') === $newDateTime->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    return false; // If parsing fails, consider them different
                }
            };

            // Log the activity with concise details
            $changes = [];
            if ($oldValues['title'] !== $request->title) $changes['title'] = ['old' => $oldValues['title'], 'new' => $request->title];
            if ($oldValues['category'] !== $request->category) $changes['category'] = ['old' => $oldValues['category'], 'new' => $request->category];
            if (!$compareDates($oldValues['start_date'], $request->start_date)) $changes['start_date'] = ['old' => $oldValues['start_date'], 'new' => $request->start_date];
            if (!$compareDates($oldValues['end_date'], $request->end_date)) $changes['end_date'] = ['old' => $oldValues['end_date'], 'new' => $request->end_date];
            
            activity()
                ->causedBy(Auth::user())
                ->performedOn($deadline)
                ->withProperties([
                    'deadline_id' => $deadline->id,
                    'title' => $deadline->title,
                    'changes' => $changes,
                ])
                ->log('updated deadline');

            return redirect()->route('admin.events')->with('success', 'Deadline updated successfully.');
        } catch (\Exception $e) {
            Log::error('Deadline update failed', [
                'error' => $e->getMessage(),
                'deadline_id' => $deadline->id,
                'request_data' => $request->all(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to update deadline: ' . $e->getMessage()]);
        }
    }

    /**
     * Extend a deadline by 1 month
     */
    public function extendDeadline(Request $request, Deadline $deadline)
    {
        try {
            $oldEndDate = $deadline->end_date;
            $newEndDate = $deadline->end_date->addMonth();

            $deadline->update([
                'end_date' => $newEndDate,
            ]);

            // Queue deadline notifications for this specific deadline after extending (non-blocking)
            $deadlineService = new CentralizedDeadlineNotificationService();
            $deadlineService->queueDeadlineNotificationsForDeadline($deadline);

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($deadline)
                ->withProperties([
                    'deadline_id' => $deadline->id,
                    'title' => $deadline->title,
                    'changes' => [
                        'end_date' => [
                            'old' => $oldEndDate,
                            'new' => $newEndDate
                        ]
                    ],
                ])
                ->log('extended deadline');

            return redirect()->route('admin.events')->with('success', "Deadline extended by 1 month successfully.");
        } catch (\Exception $e) {
            Log::error('Deadline extension failed', [
                'error' => $e->getMessage(),
                'deadline_id' => $deadline->id,
                'request_data' => $request->all(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to extend deadline: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete a deadline
     */
    public function deleteDeadline(Deadline $deadline)
    {
        try {
            // Capture deadline details before deletion for logging
            $deadlineDetails = [
                'deadline_id' => $deadline->id,
                'title' => $deadline->title,
                'category' => $deadline->category,
                'start_date' => $deadline->start_date,
                'end_date' => $deadline->end_date,
            ];

            $deadline->delete();

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'deadline_id' => $deadlineDetails['deadline_id'],
                    'title' => $deadlineDetails['title'],
                    'category' => $deadlineDetails['category'],
                ])
                ->log('deleted deadline');

            return redirect()->route('admin.events')->with('success', 'Deadline deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Deadline deletion failed', [
                'error' => $e->getMessage(),
                'deadline_id' => $deadline->id,
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to delete deadline: ' . $e->getMessage()]);
        }
    }

    /**
     * Process automatic SIP endorsements
     */
    public function processSipEndorsements()
    {
        try {
            $service = new AutomaticEndorsementService();
            $results = $service->processSipEndorsements();

            $message = "SIP Endorsements processed successfully. ";
            $message .= "Endorsed: {$results['endorsed_count']} students, ";
            $message .= "Skipped: {$results['skipped_count']} students";

            if (!empty($results['errors'])) {
                $message .= ". Errors: " . count($results['errors']);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('SIP Endorsement Processing Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to process SIP endorsements: ' . $e->getMessage()]);
        }
    }

    /**
     * Process automatic HTE placements
     */
    public function processHtePlacements()
    {
        try {
            $service = new AutomaticPlacementService();
            $results = $service->processHtePlacements();

            $message = "HTE Placements processed successfully. ";
            $message .= "Placed: {$results['placed_count']} students, ";
            $message .= "Skipped: {$results['skipped_count']} students";

            if (!empty($results['errors'])) {
                $message .= ". Errors: " . count($results['errors']);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('HTE Placement Processing Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to process HTE placements: ' . $e->getMessage()]);
        }
    }

    /**
     * Process all automatic deadlines
     */
    public function processAllDeadlines()
    {
        try {
            $sipService = new AutomaticEndorsementService();
            $hteService = new AutomaticPlacementService();

            $sipResults = $sipService->processSipEndorsements();
            $hteResults = $hteService->processHtePlacements();

            $message = "All deadlines processed successfully. ";
            $message .= "SIP Endorsed: {$sipResults['endorsed_count']} students, ";
            $message .= "HTE Placed: {$hteResults['placed_count']} students";

            $totalErrors = count($sipResults['errors']) + count($hteResults['errors']);
            if ($totalErrors > 0) {
                $message .= ". Total Errors: {$totalErrors}";
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('All Deadlines Processing Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to process deadlines: ' . $e->getMessage()]);
        }
    }

    /**
     * Get section-specific report data
     */
    private function getSectionReportData($sectionId, $reportType): array
    {
        switch ($reportType) {
            case 'student-list':
                return [
                    'students' => $this->getSectionStudents($sectionId),
                    'overviewStats' => $this->getSectionOverviewStats($sectionId),
                ];
            case 'placed-students':
                return [
                    'placedStudents' => $this->getSectionPlacedStudents($sectionId),
                    'overviewStats' => $this->getSectionOverviewStats($sectionId),
                ];
            case 'registered-students':
                return [
                    'registeredStudents' => $this->getSectionRegisteredStudents($sectionId),
                    'overviewStats' => $this->getSectionOverviewStats($sectionId),
                ];
            case 'assessment-summary':
                return [
                    'assessmentData' => $this->getSectionAssessmentData($sectionId),
                    'overviewStats' => $this->getSectionOverviewStats($sectionId),
                ];
            case 'performance-analysis':
                return [
                    'performanceData' => $this->getSectionPerformanceData($sectionId),
                    'overviewStats' => $this->getSectionOverviewStats($sectionId),
                ];
            default:
                return [];
        }
    }

    /**
     * Get general report data
     */
    private function getGeneralReportData($reportType): array
    {
        switch ($reportType) {
            case 'comprehensive':
                return [
                    'stats' => $this->getDashboardStats(),
                    'sectionStats' => $this->getSectionStats(),
                    'sectionAnalytics' => $this->getSectionAnalytics(),
                    'hteStats' => $this->getHTEStats(),
                    'allStudents' => $this->getAllStudents(),
                    'allPlacements' => $this->getAllPlacements(),
                ];
            case 'overview':
                return [
                    'stats' => $this->getDashboardStats(),
                    'sectionStats' => $this->getSectionStats(),
                ];
            case 'all-students':
                return [
                    'allStudents' => $this->getAllStudents(),
                    'stats' => $this->getDashboardStats(),
                ];
            case 'all-placements':
                return [
                    'allPlacements' => $this->getAllPlacements(),
                    'stats' => $this->getDashboardStats(),
                ];
            case 'hte-performance':
                return [
                    'hteStats' => $this->getHTEStats(),
                    'stats' => $this->getDashboardStats(),
                ];
            case 'section-comparison':
                return [
                    'sectionAnalytics' => $this->getSectionAnalytics(),
                    'stats' => $this->getDashboardStats(),
                ];
            default:
                return [];
        }
    }

    /**
     * Get section students
     */
    private function getSectionStudents($sectionId): array
    {
        return User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->where('status', '!=', 'archived')
            ->with(['academeAccounts.section', 'student.scores.subcategory.category'])
            ->get()
            ->map(function ($user) {
                $student = $user->student;
                $hasAssessment = $student && $student->is_submit;
                
                return [
                    'username' => $user->username,
                    'email' => $user->email,
                    'name' => $student ? ($student->first_name . ' ' . $student->last_name) : 'Pending',
                    'status' => $user->status,
                    'section' => $user->academeAccounts->first()->section->section_name ?? '',
                    'hasAssessment' => $hasAssessment,
                    'assessmentScore' => $hasAssessment ? $student->scores->sum('score') : 0,
                    'assessmentPercentage' => $hasAssessment ? round(($student->scores->sum('score') / ($student->scores->count() * 5)) * 100, 1) : 0,
                    'submittedAt' => $hasAssessment ? $student->updated_at->format('Y-m-d H:i:s') : null,
                ];
            })
            ->toArray();
    }

    /**
     * Get section placed students
     */
    private function getSectionPlacedStudents($sectionId): array
    {
        return StudentPlacement::whereHas('student.user.academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->with(['student.user', 'internship.hte'])
            ->get()
            ->map(function ($placement) {
                return [
                    'student_number' => $placement->student->student_number,
                    'name' => $placement->student->first_name . ' ' . $placement->student->last_name,
                    'section' => $placement->student->section->section_name ?? '',
                    'company' => $placement->internship->hte->company_name,
                    'position' => $placement->internship->position_title,
                    'department' => $placement->internship->department,
                    'compatibility_score' => $placement->compatibility_score,
                    'status' => $placement->status,
                    'placement_date' => $placement->placement_date ? $placement->placement_date->format('Y-m-d') : 'N/A',
                ];
            })
            ->toArray();
    }

    /**
     * Get section registered students
     */
    private function getSectionRegisteredStudents($sectionId): array
    {
        return User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->where('status', '!=', 'archived')
            ->with(['academeAccounts.section', 'student'])
            ->get()
            ->map(function ($user) {
                return [
                    'username' => $user->username,
                    'email' => $user->email,
                    'name' => $user->student ? ($user->student->first_name . ' ' . $user->student->last_name) : 'Pending',
                    'status' => $user->status,
                    'section' => $user->academeAccounts->first()->section->section_name ?? '',
                    'registered_at' => $user->created_at->format('Y-m-d H:i:s'),
                    'is_verified' => $user->student ? true : false,
                ];
            })
            ->toArray();
    }

    /**
     * Get section overview stats
     */
    private function getSectionOverviewStats($sectionId): array
    {
        $totalStudents = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->where('status', '!=', 'archived')
            ->count();

        $completedAssessments = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->whereHas('student', function ($query) {
                $query->where('is_submit', true);
            })
            ->where('status', '!=', 'archived')
            ->count();

        $placedStudents = StudentPlacement::whereHas('student.user.academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->where('status', 'approved')
            ->count();

        return [
            'totalStudents' => $totalStudents,
            'completedAssessments' => $completedAssessments,
            'placedStudents' => $placedStudents,
            'completionRate' => $totalStudents > 0 ? round(($completedAssessments / $totalStudents) * 100, 1) : 0,
            'placementRate' => $completedAssessments > 0 ? round(($placedStudents / $completedAssessments) * 100, 1) : 0,
        ];
    }

    /**
     * Get section assessment data
     */
    private function getSectionAssessmentData($sectionId): array
    {
        $students = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->whereHas('student', function ($query) {
                $query->where('is_submit', true);
            })
            ->where('status', '!=', 'archived')
            ->with(['student.scores.subcategory.category'])
            ->get();

        $categoryScores = [];
        $totalScores = [];

        foreach ($students as $user) {
            $student = $user->student;
            $totalScore = $student->scores->sum('score');
            $maxPossibleScore = $student->scores->count() * 5;
            $percentage = $maxPossibleScore > 0 ? round(($totalScore / $maxPossibleScore) * 100, 1) : 0;
            
            $totalScores[] = $percentage;

            foreach ($student->scores->groupBy('subcategory.category.category_name') as $categoryName => $scores) {
                if (!isset($categoryScores[$categoryName])) {
                    $categoryScores[$categoryName] = [];
                }
                $categoryScores[$categoryName][] = $scores->avg('score');
            }
        }

        $categoryAverages = [];
        foreach ($categoryScores as $category => $scores) {
            $categoryAverages[] = [
                'category' => $category,
                'averageScore' => round(array_sum($scores) / count($scores), 2),
                'totalAssessments' => count($scores),
            ];
        }

        return [
            'categoryScores' => $categoryAverages,
            'averageScore' => count($totalScores) > 0 ? round(array_sum($totalScores) / count($totalScores), 2) : 0,
            'totalAssessments' => count($totalScores),
        ];
    }

    /**
     * Get section performance data
     */
    private function getSectionPerformanceData($sectionId): array
    {
        $students = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->whereHas('student', function ($query) {
                $query->where('is_submit', true);
            })
            ->where('status', '!=', 'archived')
            ->with(['student.scores.subcategory.category'])
            ->get()
            ->map(function ($user) {
                $student = $user->student;
                $totalScore = $student->scores->sum('score');
                $maxPossibleScore = $student->scores->count() * 5;
                $percentage = $maxPossibleScore > 0 ? round(($totalScore / $maxPossibleScore) * 100, 1) : 0;
                
                return [
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'student_number' => $student->student_number,
                    'score' => $totalScore,
                    'percentage' => $percentage,
                    'submittedAt' => $student->updated_at->format('Y-m-d'),
                ];
            })
            ->sortByDesc('percentage')
            ->values()
            ->toArray();

        return [
            'topPerformers' => array_slice($students, 0, 10),
            'allStudents' => $students,
        ];
    }

    /**
     * Get all students across all sections
     */
    private function getAllStudents(): array
    {
        return User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->where('status', '!=', 'archived')
            ->with(['academeAccounts.section', 'student.scores.subcategory.category'])
            ->get()
            ->map(function ($user) {
                $student = $user->student;
                $hasAssessment = $student && $student->is_submit;
                
                return [
                    'username' => $user->username,
                    'email' => $user->email,
                    'name' => $student ? ($student->first_name . ' ' . $student->last_name) : 'Pending',
                    'status' => $user->status,
                    'section' => $user->academeAccounts->first()->section->section_name ?? '',
                    'hasAssessment' => $hasAssessment,
                    'assessmentScore' => $hasAssessment ? $student->scores->sum('score') : 0,
                    'assessmentPercentage' => $hasAssessment ? round(($student->scores->sum('score') / ($student->scores->count() * 5)) * 100, 1) : 0,
                    'submittedAt' => $hasAssessment ? $student->updated_at->format('Y-m-d H:i:s') : null,
                ];
            })
            ->toArray();
    }

    /**
     * Get all placements across all sections
     */
    private function getAllPlacements(): array
    {
        return StudentPlacement::with(['student.user', 'internship.hte'])
            ->get()
            ->map(function ($placement) {
                return [
                    'student_number' => $placement->student->student_number,
                    'name' => $placement->student->first_name . ' ' . $placement->student->last_name,
                    'section' => $placement->student->section->section_name ?? '',
                    'company' => $placement->internship->hte->company_name,
                    'position' => $placement->internship->position_title,
                    'department' => $placement->internship->department,
                    'compatibility_score' => $placement->compatibility_score,
                    'status' => $placement->status,
                    'placement_date' => $placement->placement_date ? $placement->placement_date->format('Y-m-d') : 'N/A',
                ];
            })
            ->toArray();
    }

    /**
     * Generate section CSV content
     */
    private function generateSectionCSVContent($sectionId, $reportType, $sectionName): string
    {
        $csvContent = ucwords(str_replace('-', ' ', $reportType)) . " Report - {$sectionName}\n";
        $csvContent .= "Generated: " . now()->format('F d, Y \a\t h:i A') . "\n\n";

        switch ($reportType) {
            case 'student-list':
                return $this->generateSectionStudentListCSV($sectionId, $csvContent);
            case 'placed-students':
                return $this->generateSectionPlacedStudentsCSV($sectionId, $csvContent);
            case 'registered-students':
                return $this->generateSectionRegisteredStudentsCSV($sectionId, $csvContent);
            case 'assessment-summary':
                return $this->generateSectionAssessmentSummaryCSV($sectionId, $csvContent);
            case 'performance-analysis':
                return $this->generateSectionPerformanceAnalysisCSV($sectionId, $csvContent);
            default:
                return $csvContent;
        }
    }

    /**
     * Generate general CSV content
     */
    private function generateGeneralCSVContent($reportType): string
    {
        $csvContent = ucwords(str_replace('-', ' ', $reportType)) . " Report\n";
        $csvContent .= "Generated: " . now()->format('F d, Y \a\t h:i A') . "\n\n";

        switch ($reportType) {
            case 'comprehensive':
                return $this->generateComprehensiveCSV($csvContent);
            case 'overview':
                return $this->generateOverviewCSV($csvContent);
            case 'all-students':
                return $this->generateAllStudentsCSV($csvContent);
            case 'all-placements':
                return $this->generateAllPlacementsCSV($csvContent);
            case 'hte-performance':
                return $this->generateHTEPerformanceCSV($csvContent);
            case 'section-comparison':
                return $this->generateSectionComparisonCSV($csvContent);
            default:
                return $csvContent;
        }
    }

    // CSV generation methods (simplified versions)
    private function generateSectionStudentListCSV($sectionId, $csvContent): string
    {
        $studentData = $this->getSectionStudents($sectionId);
        
        $csvContent .= "Student Information\n";
        $csvContent .= "Username,Email,Name,Status,Section,Has Assessment,Assessment Score,Assessment Percentage,Submitted At\n";
        
        foreach ($studentData as $student) {
            $csvContent .= $student['username'] . ",";
            $csvContent .= $student['email'] . ",";
            $csvContent .= '"' . $student['name'] . '",';
            $csvContent .= $student['status'] . ",";
            $csvContent .= $student['section'] . ",";
            $csvContent .= ($student['hasAssessment'] ? 'Yes' : 'No') . ",";
            $csvContent .= $student['assessmentScore'] . ",";
            $csvContent .= $student['assessmentPercentage'] . ",";
            $csvContent .= ($student['submittedAt'] ?? 'N/A') . "\n";
        }

        return $csvContent;
    }

    private function generateSectionPlacedStudentsCSV($sectionId, $csvContent): string
    {
        $placedStudents = $this->getSectionPlacedStudents($sectionId);
        
        $csvContent .= "Placed Students\n";
        $csvContent .= "Student Number,Name,Section,Company,Position,Department,Compatibility Score,Status,Placement Date\n";
        
        foreach ($placedStudents as $placement) {
            $csvContent .= $placement['student_number'] . ",";
            $csvContent .= '"' . $placement['name'] . '",';
            $csvContent .= $placement['section'] . ",";
            $csvContent .= '"' . $placement['company'] . '",';
            $csvContent .= '"' . $placement['position'] . '",';
            $csvContent .= '"' . $placement['department'] . '",';
            $csvContent .= $placement['compatibility_score'] . ",";
            $csvContent .= $placement['status'] . ",";
            $csvContent .= $placement['placement_date'] . "\n";
        }

        return $csvContent;
    }

    private function generateSectionRegisteredStudentsCSV($sectionId, $csvContent): string
    {
        $registeredStudents = $this->getSectionRegisteredStudents($sectionId);
        
        $csvContent .= "Registered Students\n";
        $csvContent .= "Username,Email,Name,Status,Section,Registered At,Is Verified\n";
        
        foreach ($registeredStudents as $student) {
            $csvContent .= $student['username'] . ",";
            $csvContent .= $student['email'] . ",";
            $csvContent .= '"' . $student['name'] . '",';
            $csvContent .= $student['status'] . ",";
            $csvContent .= $student['section'] . ",";
            $csvContent .= $student['registered_at'] . ",";
            $csvContent .= ($student['is_verified'] ? 'Yes' : 'No') . "\n";
        }

        return $csvContent;
    }

    private function generateSectionAssessmentSummaryCSV($sectionId, $csvContent): string
    {
        $assessmentData = $this->getSectionAssessmentData($sectionId);
        $overviewStats = $this->getSectionOverviewStats($sectionId);
        
        $csvContent .= "Assessment Summary\n";
        $csvContent .= "Total Students," . $overviewStats['totalStudents'] . "\n";
        $csvContent .= "Completed Assessments," . $overviewStats['completedAssessments'] . "\n";
        $csvContent .= "Completion Rate," . $overviewStats['completionRate'] . "%\n";
        $csvContent .= "Average Score," . $assessmentData['averageScore'] . "\n\n";

        $csvContent .= "Category Performance\n";
        $csvContent .= "Category,Average Score,Total Assessments\n";
        foreach ($assessmentData['categoryScores'] as $category) {
            $csvContent .= $category['category'] . ",";
            $csvContent .= $category['averageScore'] . ",";
            $csvContent .= $category['totalAssessments'] . "\n";
        }

        return $csvContent;
    }

    private function generateSectionPerformanceAnalysisCSV($sectionId, $csvContent): string
    {
        $performanceData = $this->getSectionPerformanceData($sectionId);
        
        $csvContent .= "Performance Analysis\n";
        $csvContent .= "Rank,Name,Student Number,Score,Percentage,Submitted At\n";
        
        foreach ($performanceData['allStudents'] as $index => $student) {
            $csvContent .= ($index + 1) . ",";
            $csvContent .= '"' . $student['name'] . '",';
            $csvContent .= $student['student_number'] . ",";
            $csvContent .= $student['score'] . ",";
            $csvContent .= $student['percentage'] . ",";
            $csvContent .= $student['submittedAt'] . "\n";
        }

        return $csvContent;
    }

    private function generateComprehensiveCSV($csvContent): string
    {
        $stats = $this->getDashboardStats();
        $sectionStats = $this->getSectionStats();
        $sectionAnalytics = $this->getSectionAnalytics();
        $hteStats = $this->getHTEStats();
        $allStudents = $this->getAllStudents();
        $allPlacements = $this->getAllPlacements();
        
        $csvContent .= "COMPREHENSIVE SYSTEM REPORT\n";
        $csvContent .= "Generated: " . now()->format('F d, Y \a\t h:i A') . "\n\n";
        
        // System Overview
        $csvContent .= "SYSTEM OVERVIEW\n";
        $csvContent .= "Total Students," . $stats['totalStudents'] . "\n";
        $csvContent .= "Completed Assessments," . $stats['completedAssessments'] . "\n";
        $csvContent .= "Placed Students," . $stats['placedStudents'] . "\n";
        $csvContent .= "Total HTEs," . $stats['totalHTEs'] . "\n";
        $csvContent .= "Active HTEs," . $stats['activeHTEs'] . "\n";
        $csvContent .= "Total Internships," . $stats['totalInternships'] . "\n";
        $csvContent .= "Total Slots," . $stats['totalSlots'] . "\n";
        $csvContent .= "Completion Rate," . $stats['completionRate'] . "%\n";
        $csvContent .= "Placement Rate," . $stats['placementRate'] . "%\n\n";

        // Section Performance
        $csvContent .= "SECTION PERFORMANCE\n";
        $csvContent .= "Section,Total Students,Completed Assessments,Placed Students,Completion Rate,Placement Rate,Average Score\n";
        foreach ($sectionAnalytics as $section) {
            $csvContent .= $section['section'] . ",";
            $csvContent .= $section['totalStudents'] . ",";
            $csvContent .= $section['completedAssessments'] . ",";
            $csvContent .= $section['placedStudents'] . ",";
            $csvContent .= $section['completionRate'] . "%,";
            $csvContent .= $section['placementRate'] . "%,";
            $csvContent .= $section['avgScore'] . "\n";
        }
        $csvContent .= "\n";

        // HTE Performance
        $csvContent .= "HTE PERFORMANCE\n";
        $csvContent .= "Company,Contact Person,Is Submit,Total Internships,Active Internships,Total Slots,Filled Slots,Utilization Rate,Created At\n";
        foreach ($hteStats as $hte) {
            $csvContent .= '"' . $hte['company_name'] . '",';
            $csvContent .= '"' . $hte['contact_person'] . '",';
            $csvContent .= ($hte['is_submit'] ? 'Yes' : 'No') . ",";
            $csvContent .= $hte['totalInternships'] . ",";
            $csvContent .= $hte['activeInternships'] . ",";
            $csvContent .= $hte['totalSlots'] . ",";
            $csvContent .= $hte['filledSlots'] . ",";
            $csvContent .= $hte['utilizationRate'] . "%,";
            $csvContent .= $hte['created_at'] . "\n";
        }
        $csvContent .= "\n";

        // All Students Summary
        $csvContent .= "ALL STUDENTS SUMMARY\n";
        $csvContent .= "Total Students," . count($allStudents) . "\n";
        $csvContent .= "Students with Assessments," . count(array_filter($allStudents, fn($s) => $s['hasAssessment'])) . "\n";
        $csvContent .= "Students without Assessments," . count(array_filter($allStudents, fn($s) => !$s['hasAssessment'])) . "\n\n";

        // All Placements Summary
        $csvContent .= "ALL PLACEMENTS SUMMARY\n";
        $csvContent .= "Total Placements," . count($allPlacements) . "\n";
        $csvContent .= "Approved Placements," . count(array_filter($allPlacements, fn($p) => $p['status'] === 'approved')) . "\n";
        $csvContent .= "Pending Placements," . count(array_filter($allPlacements, fn($p) => $p['status'] === 'pending')) . "\n";
        $csvContent .= "Rejected Placements," . count(array_filter($allPlacements, fn($p) => $p['status'] === 'rejected')) . "\n";

        return $csvContent;
    }

    private function generateOverviewCSV($csvContent): string
    {
        $stats = $this->getDashboardStats();
        $sectionStats = $this->getSectionStats();
        
        $csvContent .= "System Overview\n";
        $csvContent .= "Total Students," . $stats['totalStudents'] . "\n";
        $csvContent .= "Completed Assessments," . $stats['completedAssessments'] . "\n";
        $csvContent .= "Placed Students," . $stats['placedStudents'] . "\n";
        $csvContent .= "Total HTEs," . $stats['totalHTEs'] . "\n";
        $csvContent .= "Active HTEs," . $stats['activeHTEs'] . "\n";
        $csvContent .= "Total Internships," . $stats['totalInternships'] . "\n";
        $csvContent .= "Total Slots," . $stats['totalSlots'] . "\n";
        $csvContent .= "Completion Rate," . $stats['completionRate'] . "%\n";
        $csvContent .= "Placement Rate," . $stats['placementRate'] . "%\n\n";

        $csvContent .= "Section Performance\n";
        $csvContent .= "Section,Total Students,Completed Assessments,Placed Students,Completion Rate,Placement Rate\n";
        foreach ($sectionStats as $section) {
            $csvContent .= $section['section'] . ",";
            $csvContent .= $section['totalStudents'] . ",";
            $csvContent .= $section['completedAssessments'] . ",";
            $csvContent .= $section['placedStudents'] . ",";
            $csvContent .= $section['completionRate'] . "%,";
            $csvContent .= $section['placementRate'] . "%\n";
        }

        return $csvContent;
    }

    private function generateAllStudentsCSV($csvContent): string
    {
        $allStudents = $this->getAllStudents();
        
        $csvContent .= "All Students\n";
        $csvContent .= "Username,Email,Name,Status,Section,Has Assessment,Assessment Score,Assessment Percentage,Submitted At\n";
        
        foreach ($allStudents as $student) {
            $csvContent .= $student['username'] . ",";
            $csvContent .= $student['email'] . ",";
            $csvContent .= '"' . $student['name'] . '",';
            $csvContent .= $student['status'] . ",";
            $csvContent .= $student['section'] . ",";
            $csvContent .= ($student['hasAssessment'] ? 'Yes' : 'No') . ",";
            $csvContent .= $student['assessmentScore'] . ",";
            $csvContent .= $student['assessmentPercentage'] . ",";
            $csvContent .= ($student['submittedAt'] ?? 'N/A') . "\n";
        }

        return $csvContent;
    }

    private function generateAllPlacementsCSV($csvContent): string
    {
        $allPlacements = $this->getAllPlacements();
        
        $csvContent .= "All Placements\n";
        $csvContent .= "Student Number,Name,Section,Company,Position,Department,Compatibility Score,Status,Placement Date\n";
        
        foreach ($allPlacements as $placement) {
            $csvContent .= $placement['student_number'] . ",";
            $csvContent .= '"' . $placement['name'] . '",';
            $csvContent .= $placement['section'] . ",";
            $csvContent .= '"' . $placement['company'] . '",';
            $csvContent .= '"' . $placement['position'] . '",';
            $csvContent .= '"' . $placement['department'] . '",';
            $csvContent .= $placement['compatibility_score'] . ",";
            $csvContent .= $placement['status'] . ",";
            $csvContent .= $placement['placement_date'] . "\n";
        }

        return $csvContent;
    }

    private function generateHTEPerformanceCSV($csvContent): string
    {
        $hteStats = $this->getHTEStats();
        
        $csvContent .= "HTE Performance\n";
        $csvContent .= "Company,Contact Person,Is Submit,Total Internships,Active Internships,Total Slots,Filled Slots,Utilization Rate,Created At\n";
        
        foreach ($hteStats as $hte) {
            $csvContent .= '"' . $hte['company_name'] . '",';
            $csvContent .= '"' . $hte['contact_person'] . '",';
            $csvContent .= ($hte['is_submit'] ? 'Yes' : 'No') . ",";
            $csvContent .= $hte['totalInternships'] . ",";
            $csvContent .= $hte['activeInternships'] . ",";
            $csvContent .= $hte['totalSlots'] . ",";
            $csvContent .= $hte['filledSlots'] . ",";
            $csvContent .= $hte['utilizationRate'] . "%,";
            $csvContent .= $hte['created_at'] . "\n";
        }

        return $csvContent;
    }

    private function generateSectionComparisonCSV($csvContent): string
    {
        $sectionAnalytics = $this->getSectionAnalytics();
        
        $csvContent .= "Section Comparison\n";
        $csvContent .= "Section,Total Students,Completed Assessments,Placed Students,Completion Rate,Placement Rate,Average Score\n";
        
        foreach ($sectionAnalytics as $section) {
            $csvContent .= $section['section'] . ",";
            $csvContent .= $section['totalStudents'] . ",";
            $csvContent .= $section['completedAssessments'] . ",";
            $csvContent .= $section['placedStudents'] . ",";
            $csvContent .= $section['completionRate'] . "%,";
            $csvContent .= $section['placementRate'] . "%,";
            $csvContent .= $section['avgScore'] . "\n";
        }

        return $csvContent;
    }
}
