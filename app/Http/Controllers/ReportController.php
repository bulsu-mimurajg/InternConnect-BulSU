<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Show report generation page
     */
    public function index(Request $request)
    {
        $userRole = $request->user()->getRoleNames()->first() ?? 'admin';
        $sections = $this->reportService->getSections($request->user());
        $htes = $this->reportService->getHTEs($request->user());
        $internships = $this->reportService->getInternships($request->user());
        $reportCategories = $this->reportService->getReportCategories($userRole);

        return inertia('report/index', [
            'sections' => $sections,
            'htes' => $htes,
            'internships' => $internships,
            'reportCategories' => $reportCategories,
            'userRole' => $userRole,
        ]);
    }

    /**
     * Generate PDF report
     */
    public function generatePDF(Request $request, string $reportType): Response
    {
        $userRole = $request->user()->getRoleNames()->first() ?? 'admin';

        // Check if user can access this report
        if (!$this->reportService->canAccessReport($userRole, $reportType)) {
            abort(403, 'You do not have permission to access this report.');
        }

        $config = $this->reportService->getReportConfig($reportType);
        if (!$config) {
            abort(404, 'report type not found.');
        }

        // Prepare parameters
        $params = $this->prepareReportParams($request, $config);

        // Get report data
        $reportData = $this->reportService->getReportData($reportType, $params);

        // Add metadata
        $reportData['generatedAt'] = now()->format('F d, Y \a\t h:i A');
        $reportData['reportType'] = $reportType;
        $reportData['reportName'] = $config['name'];

        // Generate HTML content
        $templateName = $this->getTemplateName($userRole, $config['template']);
        $html = View::make($templateName, $reportData)->render();

        // Generate PDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        // Generate filename
        $filename = $this->generateFilename($reportType, $params, 'pdf');

        return $pdf->download($filename);
    }

    /**
     * Generate Excel report
     */
    public function generateExcel(Request $request, string $reportType): BinaryFileResponse
    {
        $userRole = $request->user()->getRoleNames()->first() ?? 'admin';

        // Check if user can access this report
        if (!$this->reportService->canAccessReport($userRole, $reportType)) {
            abort(403, 'You do not have permission to access this report.');
        }

        $config = $this->reportService->getReportConfig($reportType);
        if (!$config) {
            abort(404, 'report type not found.');
        }

        // Prepare parameters
        $params = $this->prepareReportParams($request, $config);

        // Get report data
        $reportData = $this->reportService->getReportData($reportType, $params);

        // Generate Excel content
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($config['name']);

        // Call the appropriate Excel generation method
        $excelMethod = $config['excel_method'];
        if (method_exists($this, $excelMethod)) {
            $this->$excelMethod($sheet, $reportData, $params);
        } else {
            $this->generateDefaultExcel($sheet, $reportData, $config);
        }

        // Create writer and save to temporary file
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'excel_export_');
        $writer->save($tempFile);

        // Generate filename
        $filename = $this->generateFilename($reportType, $params, 'xlsx');

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Prepare report parameters based on request and config
     */
    protected function prepareReportParams(Request $request, array $config): array
    {
        $params = [];
        $user = $request->user();
        $userRole = $user->getRoleNames()->first() ?? 'admin';
        
        // Add user and role info for data methods
        $params['user'] = $user;
        $params['user_role'] = $userRole;

        // Add section parameter if required
        if ($config['requires_section']) {
            $sectionId = $request->get('section_id', 'all');
            $params['section_id'] = $sectionId;

            if ($sectionId !== 'all') {
                $section = \App\Models\Section::find($sectionId);
                $params['section_name'] = $section ? $section->section_name : 'Unknown Section';
            } else {
                $params['section_name'] = 'All Sections';
            }
        }

        // Add HTE parameter if required OR for HTE-specific reports
        if ($config['requires_hte'] || ($userRole === 'hte' && str_starts_with($request->route('reportType'), 'hte-'))) {
            $hteId = $request->get('hte_id');
            
            // For HTE users, automatically use their own HTE ID if not provided
            if ($userRole === 'hte' && !$hteId) {
                $hteRecord = $user->hte;
                if ($hteRecord) {
                    $hteId = $hteRecord->id;
                }
            }
            
            // For admin users with HTE-specific reports that require HTE selection, require HTE selection
            // Exception: hte-internship-slots and hte-placed-students work with internships, not direct HTE selection
            $reportType = $request->route('reportType');
            $requiresDirectHTE = in_array($reportType, ['hte-profile', 'hte-performance']);
            
            if ($userRole === 'admin' && str_starts_with($reportType, 'hte-') && $requiresDirectHTE && !$hteId) {
                throw new \InvalidArgumentException('HTE selection is required for this report');
            }
            
            if ($hteId) {
                $params['hte_id'] = $hteId;
                $hte = \App\Models\HTE::find($hteId);
                $params['hte_name'] = $hte ? $hte->company_name : 'Unknown HTE';
            }
        }

        // Add internship parameter if required
        if ($config['requires_internship'] ?? false) {
            $internshipId = $request->get('internship_id');
            
            if ($internshipId && $internshipId !== 'all') {
                $params['internship_id'] = $internshipId;
                $internship = \App\Models\Internship::with('hte')->find($internshipId);
                if ($internship) {
                    $params['internship_name'] = $internship->position_title;
                    $params['hte_name'] = $internship->hte->company_name ?? 'Unknown HTE';
                    $params['hte_id'] = $internship->hte_id;
                }
            } else {
                // For "all internships" option
                if ($userRole === 'hte') {
                    // HTE users: use their own HTE
                    $hteRecord = $user->hte;
                    if ($hteRecord) {
                        $params['hte_id'] = $hteRecord->id;
                        $params['hte_name'] = $hteRecord->company_name;
                        $params['internship_id'] = 'all';
                        $params['internship_name'] = 'All Internships';
                    }
                } else {
                    // Admin users: allow all internships across all HTEs
                    $params['internship_id'] = 'all';
                    $params['internship_name'] = 'All Internships';
                    $params['hte_id'] = 'all';
                    $params['hte_name'] = 'All HTEs';
                }
            }
        }

        return $params;
    }

    /**
     * Get template name based on user role
     */
    protected function getTemplateName(string $userRole, string $templateBase): string
    {
        return "reports.{$templateBase}";
    }

    /**
     * Generate filename for download
     */
    protected function generateFilename(string $reportType, array $params, string $extension): string
    {
        $filename = str_replace('-', '_', $reportType) . '_report';

        if (isset($params['section_name']) && $params['section_name'] !== 'All Sections') {
            $filename .= '_' . str_replace(' ', '_', $params['section_name']);
        }

        if (isset($params['hte_name'])) {
            $filename .= '_' . str_replace(' ', '_', $params['hte_name']);
        }

        $filename .= '_' . now()->format('Y_m_d') . '.' . $extension;

        return $filename;
    }

    /**
     * Generate default Excel content
     */
    protected function generateDefaultExcel($sheet, array $reportData, array $config): void
    {
        $row = 1;

        // Add title
        $sheet->setCellValue("A{$row}", $config['name']);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16);
        $row += 2;

        // Add generation info
        if (isset($reportData['generatedAt'])) {
            $sheet->setCellValue("A{$row}", "Generated on: " . $reportData['generatedAt']);
            $row += 2;
        }

        // Add data based on what's available
        if (isset($reportData['students']) && $reportData['students']->isNotEmpty()) {
            $this->addStudentsToExcel($sheet, $reportData['students'], $row);
        } elseif (isset($reportData['htes']) && $reportData['htes']->isNotEmpty()) {
            $this->addHTEsToExcel($sheet, $reportData['htes'], $row);
        }
    }

    /**
     * Add students data to Excel sheet
     */
    protected function addStudentsToExcel($sheet, $students, int &$row): void
    {
        // Headers
        $headers = ['Student Number', 'Name', 'Section', 'Status'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("{$col}{$row}", $header);
            $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
            $col++;
        }
        $row++;

        // Data
        foreach ($students as $student) {
            $sheet->setCellValue("A{$row}", $student->student_number ?? '');
            $sheet->setCellValue("B{$row}", $student->user->first_name . ' ' . $student->user->last_name);
            $sheet->setCellValue("C{$row}", $student->section->section_name ?? '');
            $sheet->setCellValue("D{$row}", $student->is_active ? 'Active' : 'Inactive');
            $row++;
        }
    }

    /**
     * Add HTEs data to Excel sheet
     */
    protected function addHTEsToExcel($sheet, $htes, int &$row): void
    {
        // Headers
        $headers = ['Company Name', 'Contact Person', 'Email', 'Phone', 'Status'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("{$col}{$row}", $header);
            $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
            $col++;
        }
        $row++;

        // Data
        foreach ($htes as $hte) {
            $sheet->setCellValue("A{$row}", $hte->company_name ?? '');
            $sheet->setCellValue("B{$row}", $hte->contact_person ?? '');
            $sheet->setCellValue("C{$row}", $hte->email ?? '');
            $sheet->setCellValue("D{$row}", $hte->phone ?? '');
            $sheet->setCellValue("E{$row}", $hte->is_active ? 'Active' : 'Inactive');
            $row++;
        }
    }

    /**
     * Excel generation methods for specific report types
     */
    protected function generateStudentListExcel($sheet, array $reportData, array $params): void
    {
        $row = 1;

        // Title
        $sheet->setCellValue("A{$row}", "Student List report");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16);
        $row += 2;

        // Section info
        if (isset($params['section_name'])) {
            $sheet->setCellValue("A{$row}", "Section: " . $params['section_name']);
            $row += 2;
        }

        // Stats
        if (isset($reportData['stats'])) {
            foreach ($reportData['stats'] as $stat) {
                $sheet->setCellValue("A{$row}", $stat['label'] . ": " . $stat['value']);
                $row++;
            }
            $row++;
        }

        // Students table
        if (isset($reportData['students'])) {
            $headers = ['Student Number', 'First Name', 'Last Name', 'Middle Name', 'Section', 'Phone'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue("{$col}{$row}", $header);
                $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
                $col++;
            }
            $row++;

            foreach ($reportData['students'] as $student) {
                $sheet->setCellValue("A{$row}", $student['student_number'] ?? '');
                $sheet->setCellValue("B{$row}", $student['first_name'] ?? '');
                $sheet->setCellValue("C{$row}", $student['last_name'] ?? '');
                $sheet->setCellValue("D{$row}", $student['middle_name'] ?? '');
                $sheet->setCellValue("E{$row}", $student['section'] ?? '');
                $sheet->setCellValue("F{$row}", $student['phone'] ?? '');
                $row++;
            }
        }
    }

    protected function generateStudentAssessmentExcel($sheet, array $reportData, array $params): void
    {
        $row = 1;

        // Title
        $sheet->setCellValue("A{$row}", "Student Assessment report");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16);
        $row += 2;

        // Section info
        if (isset($params['section_name'])) {
            $sheet->setCellValue("A{$row}", "Section: " . $params['section_name']);
            $row += 2;
        }

        // Stats
        if (isset($reportData['stats'])) {
            foreach ($reportData['stats'] as $stat) {
                $sheet->setCellValue("A{$row}", $stat['label'] . ": " . $stat['value']);
                $row++;
            }
            $row++;
        }

        // Students table with assessment data
        if (isset($reportData['students'])) {
            $headers = ['Student Number', 'Name', 'Section', 'Total Score', 'Assessment Status', 'Placement Status'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue("{$col}{$row}", $header);
                $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
                $col++;
            }
            $row++;

            foreach ($reportData['students'] as $student) {
                $sheet->setCellValue("A{$row}", $student->student_number ?? '');
                $sheet->setCellValue("B{$row}", $student->last_name . ', ' . $student->first_name . ' ' . $student->middle_name);
                $sheet->setCellValue("C{$row}", $student->section->section_name ?? '');
                $sheet->setCellValue("D{$row}", $student->scores ? $student->scores->sum('score') : 'N/A');
                $sheet->setCellValue("E{$row}", $student->is_submit ? 'Assessed' : 'Not Assessed');
                $sheet->setCellValue("F{$row}", $student->placements && $student->placements->isNotEmpty() ? 'Placed' : 'Not Placed');
                $row++;
            }
        }
    }

    protected function generatePlacedStudentsExcel($sheet, array $reportData, array $params): void
    {
        $row = 1;

        // Title
        $sheet->setCellValue("A{$row}", "Placed Students report");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16);
        $row += 2;

        // Section info
        if (isset($params['section_name'])) {
            $sheet->setCellValue("A{$row}", "Section: " . $params['section_name']);
            $row += 2;
        }

        // Stats
        if (isset($reportData['stats'])) {
            foreach ($reportData['stats'] as $stat) {
                $sheet->setCellValue("A{$row}", $stat['label'] . ": " . $stat['value']);
                $row++;
            }
            $row++;
        }

        // Placed students table
        if (isset($reportData['students'])) {
            $headers = ['Student Number', 'Name', 'Section', 'Company', 'Position', 'Department', 'Status', 'Start Date'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue("{$col}{$row}", $header);
                $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
                $col++;
            }
            $row++;

            foreach ($reportData['students'] as $student) {
                $placement = $student->placements ? $student->placements->first() : null;
                $sheet->setCellValue("A{$row}", $student->student_number ?? '');
                $sheet->setCellValue("B{$row}", $student->last_name . ', ' . $student->first_name . ' ' . $student->middle_name);
                $sheet->setCellValue("C{$row}", $student->section->section_name ?? '');
                $sheet->setCellValue("D{$row}", $placement && $placement->internship && $placement->internship->hte ? $placement->internship->hte->company_name : 'N/A');
                $sheet->setCellValue("E{$row}", $placement && $placement->internship ? $placement->internship->position_title : 'N/A');
                $sheet->setCellValue("F{$row}", $placement && $placement->internship ? $placement->internship->department : 'N/A');
                $sheet->setCellValue("G{$row}", $placement ? ($placement->status === 'approved' ? 'Approved' : ucfirst($placement->status)) : 'N/A');
                $sheet->setCellValue("H{$row}", $placement ? $placement->placement_date : 'N/A');
                $row++;
            }
        }
    }

    protected function generateEndorsedStudentsExcel($sheet, array $reportData, array $params): void
    {
        $row = 1;

        // Title
        $sheet->setCellValue("A{$row}", "Endorsed Students report");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16);
        $row += 2;

        // Section info
        if (isset($params['section_name'])) {
            $sheet->setCellValue("A{$row}", "Section: " . $params['section_name']);
            $row += 2;
        }

        // Endorsement stats
        if (isset($reportData['endorsementStats'])) {
            $stats = $reportData['endorsementStats'];
            $sheet->setCellValue("A{$row}", "Total Endorsements: " . $stats['totalEndorsements']);
            $row++;
            $sheet->setCellValue("A{$row}", "Pending: " . $stats['pendingEndorsements']);
            $row++;
            $sheet->setCellValue("A{$row}", "Approved: " . $stats['approvedEndorsements']);
            $row++;
            $sheet->setCellValue("A{$row}", "Rejected: " . $stats['rejectedEndorsements']);
            $row++;
            $sheet->setCellValue("A{$row}", "Average Compatibility Score: " . $stats['averageCompatibilityScore']);
            $row += 2;
        }

        // Endorsed students table
        if (isset($reportData['students'])) {
            $headers = ['Student Number', 'Name', 'Section', 'Company', 'Position', 'Compatibility Score', 'Endorsement Status', 'Endorsement Date'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue("{$col}{$row}", $header);
                $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
                $col++;
            }
            $row++;

            foreach ($reportData['students'] as $student) {
                $endorsement = $student->endorsements ? $student->endorsements->first() : null;
                $sheet->setCellValue("A{$row}", $student->student_number ?? '');
                $sheet->setCellValue("B{$row}", $student->last_name . ', ' . $student->first_name . ' ' . $student->middle_name);
                $sheet->setCellValue("C{$row}", $student->section->section_name ?? '');
                $sheet->setCellValue("D{$row}", $endorsement && $endorsement->internship && $endorsement->internship->hte ? $endorsement->internship->hte->company_name : 'N/A');
                $sheet->setCellValue("E{$row}", $endorsement && $endorsement->internship ? $endorsement->internship->position_title : 'N/A');
                $sheet->setCellValue("F{$row}", $endorsement ? $endorsement->compatibility_score : 'N/A');
                $sheet->setCellValue("G{$row}", $endorsement ? ucfirst($endorsement->status) : 'N/A');
                $sheet->setCellValue("H{$row}", $endorsement ? $endorsement->endorsement_date : 'N/A');
                $row++;
            }
        }
    }

    protected function generatePerformanceAnalysisExcel($sheet, array $reportData, array $params): void
    {
        $row = 1;

        // Title
        $sheet->setCellValue("A{$row}", "Performance Analysis report");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16);
        $row += 2;

        // Section info
        if (isset($params['section_name'])) {
            $sheet->setCellValue("A{$row}", "Section: " . $params['section_name']);
            $row += 2;
        }

        // Performance stats
        if (isset($reportData['performanceStats'])) {
            $stats = $reportData['performanceStats'];
            $sheet->setCellValue("A{$row}", "Total Students: " . $stats['totalStudents']);
            $row++;
            $sheet->setCellValue("A{$row}", "Assessed Students: " . $stats['assessedStudents']);
            $row++;
            $sheet->setCellValue("A{$row}", "Average Score: " . $stats['averageScore']);
            $row++;
            $sheet->setCellValue("A{$row}", "Placement Rate: " . number_format($stats['placementRate'], 2) . '%');
            $row += 2;
        }

        // Students performance table
        if (isset($reportData['students'])) {
            $headers = ['Student Number', 'Name', 'Section', 'Total Score', 'Assessment Status', 'Placement Status', 'Company'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue("{$col}{$row}", $header);
                $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
                $col++;
            }
            $row++;

            foreach ($reportData['students'] as $student) {
                $placement = $student->placements ? $student->placements->first() : null;
                $sheet->setCellValue("A{$row}", $student->student_number ?? '');
                $sheet->setCellValue("B{$row}", $student->last_name . ', ' . $student->first_name . ' ' . $student->middle_name);
                $sheet->setCellValue("C{$row}", $student->section->section_name ?? '');
                $sheet->setCellValue("D{$row}", $student->scores ? $student->scores->sum('score') : 'N/A');
                $sheet->setCellValue("E{$row}", $student->is_submit ? 'Assessed' : 'Not Assessed');
                $sheet->setCellValue("F{$row}", $placement ? 'Placed' : 'Not Placed');
                $sheet->setCellValue("G{$row}", $placement && $placement->internship && $placement->internship->hte ? $placement->internship->hte->company_name : 'N/A');
                $row++;
            }
        }
    }

    protected function generateHTEPerformanceExcel($sheet, array $reportData, array $params): void
    {
        $row = 1;

        // Title
        $sheet->setCellValue("A{$row}", "HTE Performance report");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16);
        $row += 2;

        // Overall stats
        if (isset($reportData['totalHTEs'])) {
            $sheet->setCellValue("A{$row}", "Total HTEs: " . $reportData['totalHTEs']);
            $row++;
            $sheet->setCellValue("A{$row}", "Average Utilization Rate: " . number_format($reportData['averageUtilization'], 2) . '%');
            $row += 2;
        }

        // HTE performance table
        if (isset($reportData['hteStats'])) {
            $headers = ['Company Name', 'Total Slots', 'Used Slots', 'Utilization Rate', 'Average Rating'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue("{$col}{$row}", $header);
                $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
                $col++;
            }
            $row++;

            foreach ($reportData['hteStats'] as $hteStat) {
                $hte = $hteStat['hte'];
                $sheet->setCellValue("A{$row}", $hte->company_name ?? '');
                $sheet->setCellValue("B{$row}", $hteStat['totalSlots']);
                $sheet->setCellValue("C{$row}", $hteStat['usedSlots']);
                $sheet->setCellValue("D{$row}", number_format($hteStat['utilizationRate'], 2) . '%');
                $sheet->setCellValue("E{$row}", $hteStat['averageRating']);
                $row++;
            }
        }
    }

    protected function generateComprehensiveExcel($sheet, array $reportData, array $params): void
    {
        $row = 1;

        // Title
        $sheet->setCellValue("A{$row}", "Comprehensive System Report");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16);
        $row += 2;

        // Overall stats
        if (isset($reportData['overallStats'])) {
            $stats = $reportData['overallStats'];
            $sheet->setCellValue("A{$row}", "=== SYSTEM OVERVIEW ===");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
            $row += 2;
            
            $sheet->setCellValue("A{$row}", "Total Students: " . $stats['totalStudents']);
            $row++;
            $sheet->setCellValue("A{$row}", "Active Students: " . $stats['activeStudents']);
            $row++;
            $sheet->setCellValue("A{$row}", "Assessed Students: " . $stats['assessedStudents']);
            $row++;
            $sheet->setCellValue("A{$row}", "Average Score: " . $stats['averageScore']);
            $row++;
            $sheet->setCellValue("A{$row}", "Placement Rate: " . $stats['placementRate'] . '%');
            $row += 2;
            
            $sheet->setCellValue("A{$row}", "Total HTEs: " . $stats['totalHTEs']);
            $row++;
            $sheet->setCellValue("A{$row}", "Total Slots: " . $stats['totalSlots']);
            $row++;
            $sheet->setCellValue("A{$row}", "Used Slots: " . $stats['usedSlots']);
            $row++;
            $sheet->setCellValue("A{$row}", "Utilization Rate: " . $stats['utilizationRate'] . '%');
            $row++;
            $sheet->setCellValue("A{$row}", "Endorsement Rate: " . $stats['endorsementRate'] . '%');
            $row += 2;
        }

        // Top performing sections
        if (isset($reportData['sectionStats'])) {
            $sheet->setCellValue("A{$row}", "=== TOP PERFORMING SECTIONS ===");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
            $row += 2;

            $headers = ['Section Name', 'Total Students', 'Assessed Students', 'Average Score', 'Placements', 'Placement Rate'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue("{$col}{$row}", $header);
                $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
                $col++;
            }
            $row++;

            foreach ($reportData['sectionStats'] as $stat) {
                $sheet->setCellValue("A{$row}", $stat['section']->section_name);
                $sheet->setCellValue("B{$row}", $stat['section']->students_count ?? 0);
                $sheet->setCellValue("C{$row}", $stat['assessedCount']);
                $sheet->setCellValue("D{$row}", $stat['averageScore']);
                $sheet->setCellValue("E{$row}", $stat['placementCount']);
                $sheet->setCellValue("F{$row}", $stat['placementRate'] . '%');
                $row++;
            }
            $row += 2;
        }

        // Top performing HTEs
        if (isset($reportData['hteStats'])) {
            $sheet->setCellValue("A{$row}", "=== TOP PERFORMING HTEs ===");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
            $row += 2;

            $headers = ['Company Name', 'Contact Person', 'Total Slots', 'Used Slots', 'Utilization Rate', 'Status'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue("{$col}{$row}", $header);
                $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
                $col++;
            }
            $row++;

            foreach ($reportData['hteStats'] as $stat) {
                $sheet->setCellValue("A{$row}", $stat['hte']->company_name ?? 'N/A');
                $sheet->setCellValue("B{$row}", $stat['hte']->getContactPersonFullNameAttribute() ?? 'N/A');
                $sheet->setCellValue("C{$row}", $stat['totalSlots']);
                $sheet->setCellValue("D{$row}", $stat['usedSlots']);
                $sheet->setCellValue("E{$row}", $stat['utilizationRate'] . '%');
                $sheet->setCellValue("F{$row}", $stat['hte']->is_active ? 'Active' : 'Inactive');
                $row++;
            }
            $row += 2;
        }

        // Placement & Endorsement Summary
        if (isset($reportData['overallStats'])) {
            $stats = $reportData['overallStats'];
            $sheet->setCellValue("A{$row}", "=== PLACEMENT & ENDORSEMENT SUMMARY ===");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
            $row += 2;

            $sheet->setCellValue("A{$row}", "Placement Status:");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;
            $sheet->setCellValue("A{$row}", "  Approved: " . $stats['approvedPlacements']);
            $row++;
            $sheet->setCellValue("A{$row}", "  Pending: " . $stats['pendingPlacements']);
            $row++;
            $sheet->setCellValue("A{$row}", "  Rejected: " . $stats['rejectedPlacements']);
            $row++;
            $sheet->setCellValue("A{$row}", "  Total: " . $stats['totalPlacements']);
            $row += 2;

            $sheet->setCellValue("A{$row}", "Endorsement Status:");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;
            $sheet->setCellValue("A{$row}", "  Approved: " . $stats['approvedEndorsements']);
            $row++;
            $sheet->setCellValue("A{$row}", "  Pending: " . $stats['pendingEndorsements']);
            $row++;
            $sheet->setCellValue("A{$row}", "  Rejected: " . $stats['rejectedEndorsements']);
            $row++;
            $sheet->setCellValue("A{$row}", "  Total: " . $stats['totalEndorsements']);
        }
    }

    protected function generateHTEProfileExcel($sheet, array $reportData, array $params): void
    {
        $row = 1;

        // Title
        $sheet->setCellValue("A{$row}", "HTE Profile Report");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16);
        $row += 2;

        // HTE Company Information
        if (isset($reportData['hte'])) {
            $hte = $reportData['hte'];
            $sheet->setCellValue("A{$row}", "Company: " . $hte->company_name);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
            $row++;
            
            $sheet->setCellValue("A{$row}", "Address: " . ($hte->company_address ?? 'Not specified'));
            $row++;
            $sheet->setCellValue("A{$row}", "Email: " . ($hte->company_email ?? 'Not specified'));
            $row++;
            
            $contactPerson = trim(($hte->cperson_fname ?? '') . ' ' . ($hte->cperson_lname ?? ''));
            $sheet->setCellValue("A{$row}", "Contact Person: " . ($contactPerson ?: 'Not specified'));
            $row++;
            $sheet->setCellValue("A{$row}", "Position: " . ($hte->cperson_position ?? 'Not specified'));
            $row++;
            $sheet->setCellValue("A{$row}", "Contact Number: " . ($hte->cperson_contactnum ?? 'Not specified'));
            $row++;
            $sheet->setCellValue("A{$row}", "Status: " . ($hte->is_active ? 'Active' : 'Inactive'));
            $row++;
            $sheet->setCellValue("A{$row}", "Profile Submitted: " . ($hte->is_submit ? 'Yes' : 'No'));
            $row += 2;
        }

        // Statistics
        if (isset($reportData['stats'])) {
            $sheet->setCellValue("A{$row}", "Statistics Overview");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
            $row++;
            
            foreach ($reportData['stats'] as $stat) {
                $sheet->setCellValue("A{$row}", $stat['label'] . ": " . $stat['value']);
                $row++;
            }
            $row++;
        }

        // Department Breakdown
        if (isset($reportData['departmentStats']) && $reportData['departmentStats']->count() > 0) {
            $sheet->setCellValue("A{$row}", "Department Breakdown");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
            $row++;
            
            // Headers
            $sheet->setCellValue("A{$row}", "Department");
            $sheet->setCellValue("B{$row}", "Internships");
            $sheet->setCellValue("C{$row}", "Total Slots");
            $sheet->setCellValue("D{$row}", "Used Slots");
            $sheet->setCellValue("E{$row}", "Utilization Rate");
            $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
            $row++;
            
            foreach ($reportData['departmentStats'] as $dept) {
                $sheet->setCellValue("A{$row}", $dept['department']);
                $sheet->setCellValue("B{$row}", $dept['internships_count']);
                $sheet->setCellValue("C{$row}", $dept['total_slots']);
                $sheet->setCellValue("D{$row}", $dept['used_slots']);
                $sheet->setCellValue("E{$row}", number_format($dept['utilization_rate'], 1) . '%');
                $row++;
            }
            $row += 2;
        }

        // Internship Details
        if (isset($reportData['internships']) && $reportData['internships']->count() > 0) {
            $sheet->setCellValue("A{$row}", "Internship Offerings");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
            $row++;
            
            // Headers
            $sheet->setCellValue("A{$row}", "Position Title");
            $sheet->setCellValue("B{$row}", "Department");
            $sheet->setCellValue("C{$row}", "Total Slots");
            $sheet->setCellValue("D{$row}", "Used Slots");
            $sheet->setCellValue("E{$row}", "Available Slots");
            $sheet->setCellValue("F{$row}", "Utilization Rate");
            $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);
            $row++;
            
            foreach ($reportData['internships'] as $internship) {
                $usedSlots = $internship->studentPlacements->where('status', 'approved')->count();
                $utilizationRate = $internship->slot_count > 0 ? ($usedSlots / $internship->slot_count) * 100 : 0;
                
                $sheet->setCellValue("A{$row}", $internship->position_title);
                $sheet->setCellValue("B{$row}", $internship->department);
                $sheet->setCellValue("C{$row}", $internship->slot_count);
                $sheet->setCellValue("D{$row}", $usedSlots);
                $sheet->setCellValue("E{$row}", $internship->slot_count - $usedSlots);
                $sheet->setCellValue("F{$row}", number_format($utilizationRate, 1) . '%');
                $row++;
            }
            $row += 2;
        }

        // Placed Students Summary
        if (isset($reportData['placedStudents']) && $reportData['placedStudents']->count() > 0) {
            $sheet->setCellValue("A{$row}", "Placed Students Summary");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
            $row++;
            
            // Headers
            $sheet->setCellValue("A{$row}", "Student Name");
            $sheet->setCellValue("B{$row}", "Student Number");
            $sheet->setCellValue("C{$row}", "Section");
            $sheet->setCellValue("D{$row}", "Position");
            $sheet->setCellValue("E{$row}", "Department");
            $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
            $row++;
            
            foreach ($reportData['placedStudents']->take(50) as $student) {
                $placement = $student->placements->where('status', 'approved')->first();
                $internship = $placement ? $placement->internship : null;
                
                $studentName = $student->last_name . ', ' . $student->first_name;
                if ($student->middle_name) {
                    $studentName .= ' ' . strtoupper(substr($student->middle_name, 0, 1)) . '.';
                }
                
                $sheet->setCellValue("A{$row}", $studentName);
                $sheet->setCellValue("B{$row}", $student->student_number);
                $sheet->setCellValue("C{$row}", $student->section->section_name ?? 'N/A');
                $sheet->setCellValue("D{$row}", $internship ? $internship->position_title : 'N/A');
                $sheet->setCellValue("E{$row}", $internship ? $internship->department : 'N/A');
                $row++;
            }
            
            if ($reportData['placedStudents']->count() > 50) {
                $sheet->setCellValue("A{$row}", "Note: Showing first 50 of " . $reportData['placedStudents']->count() . " placed students.");
                $sheet->getStyle("A{$row}")->getFont()->setItalic(true);
            }
        }

        // Auto-size columns
        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    protected function generateHTEInternshipSlotsExcel($sheet, array $reportData, array $params): void
    {
        $row = 1;

        // Title
        $sheet->setCellValue("A{$row}", "HTE Internship Slots report");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16);
        $row += 2;

        // HTE info
        if (isset($reportData['hte']) && $reportData['hte']) {
            $sheet->setCellValue("A{$row}", "Company: " . $reportData['hte']->company_name);
        } else {
            $sheet->setCellValue("A{$row}", "Company: All HTEs");
        }
        $row += 2;

        // Slot stats
        if (isset($reportData['totalSlots'])) {
            $sheet->setCellValue("A{$row}", "Total Slots: " . $reportData['totalSlots']);
            $row++;
            $sheet->setCellValue("A{$row}", "Used Slots: " . $reportData['usedSlots']);
            $row++;
            $sheet->setCellValue("A{$row}", "Available Slots: " . ($reportData['totalSlots'] - $reportData['usedSlots']));
            $row += 2;
        }

        // Slots table
        if (isset($reportData['slotStats'])) {
            $headers = ['Position Title', 'Department', 'Available Slots', 'Used Slots', 'Utilization Rate'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue("{$col}{$row}", $header);
                $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
                $col++;
            }
            $row++;

            foreach ($reportData['slotStats'] as $slotStat) {
                $internship = $slotStat['internship'];
                $sheet->setCellValue("A{$row}", $internship->position_title);
                $sheet->setCellValue("B{$row}", $internship->department);
                $sheet->setCellValue("C{$row}", $internship->slot_count);
                $sheet->setCellValue("D{$row}", $slotStat['usedSlots']);
                $sheet->setCellValue("E{$row}", number_format($slotStat['utilizationRate'], 2) . '%');
                $row++;
            }
        }
    }

    protected function generateHTEPlacedStudentsExcel($sheet, array $reportData, array $params): void
    {
        $row = 1;

        // Title
        $sheet->setCellValue("A{$row}", "HTE Placed Students report");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16);
        $row += 2;

        // HTE info
        if (isset($reportData['hte']) && $reportData['hte']) {
            $sheet->setCellValue("A{$row}", "Company: " . $reportData['hte']->company_name);
        } else {
            $sheet->setCellValue("A{$row}", "Company: All HTEs");
        }
        $row += 2;

        // Stats
        if (isset($reportData['stats'])) {
            foreach ($reportData['stats'] as $stat) {
                $sheet->setCellValue("A{$row}", $stat['label'] . ": " . $stat['value']);
                $row++;
            }
            $row++;
        }

        // Placed students table
        if (isset($reportData['students'])) {
            $headers = ['Student Number', 'Name', 'Section', 'Position', 'Department', 'Status', 'Start Date'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue("{$col}{$row}", $header);
                $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
                $col++;
            }
            $row++;

            foreach ($reportData['students'] as $student) {
                $placement = $student->placements ? $student->placements->first() : null;
                $sheet->setCellValue("A{$row}", $student->student_number ?? '');
                $sheet->setCellValue("B{$row}", $student->last_name . ', ' . $student->first_name . ' ' . $student->middle_name);
                $sheet->setCellValue("C{$row}", $student->section->section_name ?? '');
                $sheet->setCellValue("D{$row}", $placement && $placement->internship ? $placement->internship->position_title : 'N/A');
                $sheet->setCellValue("E{$row}", $placement && $placement->internship ? $placement->internship->department : 'N/A');
                $sheet->setCellValue("F{$row}", $placement ? ($placement->status === 'approved' ? 'Approved' : ucfirst($placement->status)) : 'N/A');
                $sheet->setCellValue("G{$row}", $placement ? $placement->placement_date : 'N/A');
                $row++;
            }
        }
    }

    protected function generateAdviserPerformanceExcel($sheet, array $reportData, array $params): void
    {
        $row = 1;

        // Title
        $sheet->setCellValue("A{$row}", "Adviser Performance Report");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16);
        $row += 2;

        // Overall stats
        if (isset($reportData['overallStats'])) {
            $stats = $reportData['overallStats'];
            $sheet->setCellValue("A{$row}", "=== SYSTEM OVERVIEW ===");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
            $row += 2;
            
            $sheet->setCellValue("A{$row}", "Total Advisers: " . $stats['totalAdvisers']);
            $row++;
            $sheet->setCellValue("A{$row}", "Active Advisers: " . $stats['activeAdvisers']);
            $row++;
            $sheet->setCellValue("A{$row}", "Total Sections: " . $stats['totalSections']);
            $row++;
            $sheet->setCellValue("A{$row}", "Average Sections per Adviser: " . $stats['averageSectionsPerAdviser']);
            $row++;
            $sheet->setCellValue("A{$row}", "Average Score: " . number_format($stats['averageScore'], 2));
            $row++;
            $sheet->setCellValue("A{$row}", "Average Placement Rate: " . number_format($stats['averagePlacementRate'], 2) . '%');
            $row++;
            $sheet->setCellValue("A{$row}", "Average Endorsement Rate: " . number_format($stats['averageEndorsementRate'], 2) . '%');
            $row += 2;
        }

        // Adviser performance table
        if (isset($reportData['adviserStats'])) {
            $sheet->setCellValue("A{$row}", "=== ADVISER PERFORMANCE OVERVIEW ===");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
            $row += 2;

            $headers = ['Adviser Name', 'Status', 'Sections', 'Total Students', 'Assessed Students', 'Average Score', 'Placement Rate', 'Endorsement Rate'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue("{$col}{$row}", $header);
                $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
                $col++;
            }
            $row++;

            foreach ($reportData['adviserStats'] as $stat) {
                $sheet->setCellValue("A{$row}", $stat['adviser']->adviser_fname . ' ' . $stat['adviser']->adviser_lname);
                $sheet->setCellValue("B{$row}", $stat['adviser']->is_active ? 'Active' : 'Inactive');
                $sheet->setCellValue("C{$row}", $stat['totalSections']);
                $sheet->setCellValue("D{$row}", $stat['totalStudents']);
                $sheet->setCellValue("E{$row}", $stat['assessedStudents']);
                $sheet->setCellValue("F{$row}", $stat['averageScore']);
                $sheet->setCellValue("G{$row}", $stat['placementRate'] . '%');
                $sheet->setCellValue("H{$row}", $stat['endorsementRate'] . '%');
                $row++;
            }
            $row += 2;
        }

        // Section-wise performance
        if (isset($reportData['adviserSectionStats'])) {
            $sheet->setCellValue("A{$row}", "=== SECTION-WISE PERFORMANCE ===");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
            $row += 2;

            foreach ($reportData['adviserSectionStats'] as $adviserId => $sections) {
                $adviser = $reportData['advisers']->firstWhere('id', $adviserId);
                if ($adviser && $sections->isNotEmpty()) {
                    $sheet->setCellValue("A{$row}", $adviser->adviser_fname . ' ' . $adviser->adviser_lname);
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
                    $row++;

                    $headers = ['Section Name', 'Total Students', 'Assessed Students', 'Average Score', 'Placed Students', 'Placement Rate'];
                    $col = 'A';
                    foreach ($headers as $header) {
                        $sheet->setCellValue("{$col}{$row}", $header);
                        $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
                        $col++;
                    }
                    $row++;

                    foreach ($sections as $sectionStat) {
                        $sheet->setCellValue("A{$row}", $sectionStat['section']->section_name);
                        $sheet->setCellValue("B{$row}", $sectionStat['totalStudents']);
                        $sheet->setCellValue("C{$row}", $sectionStat['assessedStudents']);
                        $sheet->setCellValue("D{$row}", $sectionStat['averageScore']);
                        $sheet->setCellValue("E{$row}", $sectionStat['placedStudents']);
                        $sheet->setCellValue("F{$row}", $sectionStat['placementRate'] . '%');
                        $row++;
                    }
                    $row += 2;
                }
            }
        }
    }
}
