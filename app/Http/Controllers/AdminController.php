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
use Barryvdh\DomPDF\Facade\Pdf;
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

        return Inertia::render('admin/dashboard', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'placementOverview' => $placementOverview,
            'sectionStats' => $sectionStats,
            'hteStats' => $hteStats,
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
        // Placements by status
        $placementsByStatus = StudentPlacement::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

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

                return [
                    'id' => $hte->id,
                    'company_name' => $hte->company_name,
                    'contact_person' => $hte->cperson_fname . ' ' . $hte->cperson_lname,
                    'email' => $hte->company_email,
                    'is_submit' => $hte->is_submit,
                    'totalInternships' => $totalInternships,
                    'activeInternships' => $activeInternships,
                    'totalSlots' => $totalSlots,
                    'created_at' => $hte->created_at->format('M d, Y'),
                ];
            })
            ->sortByDesc('totalSlots')
            ->take(10)
            ->values()
            ->toArray();
    }

    /**
     * Display comprehensive reports page
     */
    public function report(): Response
    {
        // Get comprehensive statistics
        $stats = $this->getDashboardStats();
        
        // Get detailed placement analytics
        $placementAnalytics = $this->getPlacementAnalytics();
        
        // Get student performance analytics
        $studentAnalytics = $this->getStudentAnalytics();
        
        // Get HTE performance analytics
        $hteAnalytics = $this->getHTEAnalytics();
        
        // Get section performance analytics
        $sectionAnalytics = $this->getSectionAnalytics();
        
        // Get assessment completion trends
        $assessmentTrends = $this->getAssessmentTrends();
        
        // Get placement trends
        $placementTrends = $this->getPlacementTrends();

        return Inertia::render('admin/report', [
            'stats' => $stats,
            'placementAnalytics' => $placementAnalytics,
            'studentAnalytics' => $studentAnalytics,
            'hteAnalytics' => $hteAnalytics,
            'sectionAnalytics' => $sectionAnalytics,
            'assessmentTrends' => $assessmentTrends,
            'placementTrends' => $placementTrends,
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
        $htes = HTE::with('user')
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
        $htes = HTE::with('user')
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

            return redirect()->route('admin.hte')->with('success', 'HTE account created successfully.');
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
        $advisers = Adviser::with(['user', 'section'])
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
                    'section_id' => $adviser->section_id,
                    'section_name' => $adviser->section ? $adviser->section->section_name : 'No Section',
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
            'section_id' => 'required|exists:sections,section_id',
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
            Adviser::create([
                'user_id' => $user->id,
                'adviser_fname' => $request->adviser_fname,
                'adviser_lname' => $request->adviser_lname,
                'section_id' => $request->section_id,
                'is_active' => true,
            ]);

            DB::commit();

            return redirect()->route('admin.adviser')->with('success', 'Adviser account created successfully.');
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
            'section_id' => 'required|exists:sections,section_id',
        ]);

        try {
            DB::beginTransaction();

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
                'section_id' => $request->section_id,
            ]);

            DB::commit();

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
        $advisers = Adviser::with(['user', 'section'])
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
                    'section_id' => $adviser->section_id,
                    'section_name' => $adviser->section ? $adviser->section->section_name : 'No Section',
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

            return redirect()->route('admin.adviser.archived')->with('success', 'Adviser account unarchived successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to unarchive adviser account. Please try again.']);
        }
    }

    /**
     * Display Events management page
     */
    public function eventsManagement(): Response
    {
        // Get deadlines
        $deadlines = Deadline::orderBy('created_at', 'desc')
            ->get()
            ->map(function ($deadline) {
                return [
                    'id' => $deadline->id,
                    'start_date' => $deadline->start_date->format('Y-m-d\TH:i'),
                    'end_date' => $deadline->end_date->format('Y-m-d\TH:i'),
                    'status' => $deadline->status,
                    'is_active' => $deadline->isActive(),
                    'is_expired' => $deadline->isExpired(),
                    'created_at' => $deadline->created_at->format('M d, Y'),
                    'updated_at' => $deadline->updated_at->format('M d, Y'),
                ];
            });

        return Inertia::render('admin/events', [
            'deadlines' => $deadlines,
        ]);
    }

    /**
     * Store a new deadline
     */
    public function storeDeadline(Request $request)
    {
        // Debug: Log incoming request data
        Log::info('Deadline Creation Request', [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'all_request_data' => $request->all(),
        ]);

        try {
            $request->validate([
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
            $deadline = Deadline::create([
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            Log::info('Deadline created successfully', [
                'deadline_id' => $deadline->id,
                'start_date' => $deadline->start_date,
                'end_date' => $deadline->end_date,
                'status' => $deadline->status,
            ]);

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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $deadline->update([
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

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
     * Delete a deadline
     */
    public function deleteDeadline(Deadline $deadline)
    {
        try {
            $deadline->delete();

            return redirect()->route('admin.events')->with('success', 'Deadline deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Deadline deletion failed', [
                'error' => $e->getMessage(),
                'deadline_id' => $deadline->id,
            ]);
            
            return redirect()->back()->withErrors(['error' => 'Failed to delete deadline: ' . $e->getMessage()]);
        }
    }
}
