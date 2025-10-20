<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TestReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Show test report dashboard with all available reports
     */
    public function index(Request $request)
    {
        $userRole = $request->user()->role ?? 'admin';
        $availableReports = $this->reportService->getAvailableReports($userRole);
        
        // Get test data for dropdowns
        $testSections = $this->getTestSections();
        $testHTEs = $this->getTestHTEs();
        $testInternships = $this->getTestInternships();

        return inertia('test-reports/index', [
            'availableReports' => $availableReports,
            'testSections' => $testSections,
            'testHTEs' => $testHTEs,
            'testInternships' => $testInternships,
            'userRole' => $userRole,
        ]);
    }

    /**
     * Generate test PDF report
     */
    public function generateTestPDF(Request $request, string $reportType): Response
    {
        $userRole = $request->user()->role ?? 'admin';

        // Check if user can access this report
        if (!$this->reportService->canAccessReport($userRole, $reportType)) {
            abort(403, 'You do not have permission to access this report.');
        }

        $config = $this->reportService->getReportConfig($reportType);
        if (!$config) {
            abort(404, 'Report type not found.');
        }

        try {
            // Generate test data with parameters
            $params = $this->getTestParameters($request, $config);
            $data = $this->generateTestData($reportType, $params);

            // Generate PDF with template fallback
            $template = "reports.test.{$config['template']}";
            if (!view()->exists($template)) {
                $template = 'reports.test.generic';
            }
            
            $pdf = Pdf::loadView($template, [
                'data' => $data,
                'reportConfig' => $config,
                'generatedAt' => now(),
                'isTest' => true,
            ]);

            $filename = "test_{$reportType}_" . now()->format('Y-m-d_H-i-s') . '.pdf';
            
            return $pdf->stream($filename, ['Attachment' => false]);

        } catch (\Exception $e) {
            Log::error('Test PDF generation failed', [
                'reportType' => $reportType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            abort(500, 'Failed to generate test PDF: ' . $e->getMessage());
        }
    }

    /**
     * Generate test Excel report
     */
    public function generateTestExcel(Request $request, string $reportType): BinaryFileResponse
    {
        $userRole = $request->user()->role ?? 'admin';

        // Check if user can access this report
        if (!$this->reportService->canAccessReport($userRole, $reportType)) {
            abort(403, 'You do not have permission to access this report.');
        }

        $config = $this->reportService->getReportConfig($reportType);
        if (!$config) {
            abort(404, 'Report type not found.');
        }

        try {
            // Generate test data with parameters
            $params = $this->getTestParameters($request, $config);
            $data = $this->generateTestData($reportType, $params);

            // Generate Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set test data in Excel format
            $this->populateExcelSheet($sheet, $data, $config);

            $filename = "test_{$reportType}_" . now()->format('Y-m-d_H-i-s') . '.xlsx';
            $filePath = storage_path('app/temp/' . $filename);
            
            // Ensure temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);

            return response()->download($filePath, $filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Test Excel generation failed', [
                'reportType' => $reportType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            abort(500, 'Failed to generate test Excel: ' . $e->getMessage());
        }
    }

    /**
     * Preview test report data (JSON response)
     */
    public function previewTestData(Request $request, string $reportType)
    {
        $userRole = $request->user()->role ?? 'admin';

        // Check if user can access this report
        if (!$this->reportService->canAccessReport($userRole, $reportType)) {
            abort(403, 'You do not have permission to access this report.');
        }

        $config = $this->reportService->getReportConfig($reportType);
        if (!$config) {
            abort(404, 'Report type not found.');
        }

        try {
            // Generate test data with parameters
            $params = $this->getTestParameters($request, $config);
            $data = $this->generateTestData($reportType, $params);

            return response()->json([
                'success' => true,
                'reportType' => $reportType,
                'reportConfig' => $config,
                'testData' => $data,
                'parameters' => $params,
                'generatedAt' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Test data preview failed', [
                'reportType' => $reportType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate test data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get test parameters based on report configuration
     */
    private function getTestParameters(Request $request, array $config): array
    {
        $params = [];

        if ($config['requires_section'] ?? false) {
            $params['section_id'] = $request->get('section_id', 1);
        }

        if ($config['requires_hte'] ?? false) {
            $params['hte_id'] = $request->get('hte_id', 1);
        }

        if ($config['requires_internship'] ?? false) {
            $params['internship_id'] = $request->get('internship_id', 1);
        }

        return $params;
    }

    /**
     * Generate test data for specific report type
     */
    private function generateTestData(string $reportType, array $params): array
    {
        $method = 'generateTestData' . str_replace('-', '', ucwords($reportType, '-'));
        
        if (method_exists($this, $method)) {
            return $this->$method($params);
        }

        // Fallback to generic test data
        return $this->generateGenericTestData($reportType, $params);
    }

    /**
     * Generate test data for comprehensive report
     */
    private function generateTestDataComprehensive(array $params): array
    {
        return [
            'summary' => [
                'total_students' => 150,
                'total_htes' => 25,
                'total_internships' => 45,
                'placed_students' => 120,
                'placement_rate' => 80.0,
            ],
            'sections' => [
                ['name' => 'CS-3A', 'students' => 30, 'placed' => 25, 'rate' => 83.3],
                ['name' => 'CS-3B', 'students' => 28, 'placed' => 22, 'rate' => 78.6],
                ['name' => 'IT-3A', 'students' => 32, 'placed' => 28, 'rate' => 87.5],
            ],
            'htes' => [
                ['name' => 'TechCorp Inc.', 'internships' => 5, 'slots' => 15, 'utilization' => 93.3],
                ['name' => 'DataSoft Ltd.', 'internships' => 3, 'slots' => 12, 'utilization' => 83.3],
            ],
            'generated_at' => now(),
        ];
    }

    /**
     * Generate test data for HTE performance report
     */
    private function generateTestDataHtePerformance(array $params): array
    {
        return [
            'htes' => [
                [
                    'id' => 1,
                    'company_name' => 'TechCorp Inc.',
                    'total_internships' => 5,
                    'total_slots' => 15,
                    'filled_slots' => 14,
                    'utilization_rate' => 93.3,
                    'avg_compatibility' => 85.2,
                    'response_time' => '2.5 days',
                ],
                [
                    'id' => 2,
                    'company_name' => 'DataSoft Ltd.',
                    'total_internships' => 3,
                    'total_slots' => 12,
                    'filled_slots' => 10,
                    'utilization_rate' => 83.3,
                    'avg_compatibility' => 78.9,
                    'response_time' => '3.2 days',
                ],
            ],
            'summary' => [
                'total_htes' => 25,
                'avg_utilization' => 78.5,
                'avg_response_time' => '2.8 days',
            ],
            'generated_at' => now(),
        ];
    }

    /**
     * Generate test data for student list report
     */
    private function generateTestDataStudentList(array $params): array
    {
        $students = [];
        for ($i = 1; $i <= 30; $i++) {
            $students[] = [
                'id' => $i,
                'student_number' => '2024-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'first_name' => 'Student',
                'last_name' => 'Name' . $i,
                'middle_name' => 'M',
                'section' => 'CS-3A',
                'specialization' => 'Web Development',
                'is_active' => true,
                'created_at' => now()->subDays(rand(1, 30)),
            ];
        }

        return [
            'students' => $students,
            'section' => 'CS-3A',
            'total_count' => 30,
            'generated_at' => now(),
        ];
    }

    /**
     * Generate test data for placed students report
     */
    private function generateTestDataPlacedStudents(array $params): array
    {
        $placedStudents = [];
        for ($i = 1; $i <= 20; $i++) {
            $placedStudents[] = [
                'id' => $i,
                'student_number' => '2024-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'first_name' => 'Student',
                'last_name' => 'Name' . $i,
                'section' => 'CS-3A',
                'company_name' => 'TechCorp Inc.',
                'position_title' => 'Software Developer Intern',
                'compatibility_score' => rand(70, 95),
                'placement_date' => now()->subDays(rand(1, 15)),
            ];
        }

        return [
            'placed_students' => $placedStudents,
            'section' => 'CS-3A',
            'total_count' => 20,
            'generated_at' => now(),
        ];
    }

    /**
     * Generate test data for HTE profile report
     */
    private function generateTestDataHteProfile(array $params): array
    {
        return [
            'hte' => [
                'id' => 1,
                'company_name' => 'TechCorp Inc.',
                'contact_person' => 'John Smith',
                'email' => 'john@techcorp.com',
                'phone' => '+1-555-0123',
                'address' => '123 Tech Street, Silicon Valley, CA',
                'website' => 'https://techcorp.com',
                'description' => 'Leading technology company specializing in software development.',
            ],
            'internships' => [
                [
                    'id' => 1,
                    'position_title' => 'Software Developer Intern',
                    'slot_count' => 5,
                    'filled_slots' => 4,
                    'duration' => '6 months',
                    'start_date' => '2024-01-15',
                ],
                [
                    'id' => 2,
                    'position_title' => 'Data Analyst Intern',
                    'slot_count' => 3,
                    'filled_slots' => 3,
                    'duration' => '4 months',
                    'start_date' => '2024-02-01',
                ],
            ],
            'generated_at' => now(),
        ];
    }

    /**
     * Generate generic test data for any report type
     */
    private function generateGenericTestData(string $reportType, array $params): array
    {
        return [
            'report_type' => $reportType,
            'parameters' => $params,
            'message' => 'This is test data for ' . $reportType . ' report',
            'generated_at' => now(),
            'note' => 'Generic test data - specific implementation needed for ' . $reportType,
        ];
    }

    /**
     * Populate Excel sheet with test data
     */
    private function populateExcelSheet($sheet, array $data, array $config): void
    {
        $row = 1;
        
        // Add header
        $sheet->setCellValue('A' . $row, 'Test Report: ' . $config['name']);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row += 2;

        // Add generated timestamp
        $sheet->setCellValue('A' . $row, 'Generated: ' . now()->format('Y-m-d H:i:s'));
        $row += 2;

        // Add data based on report type
        if (isset($data['students'])) {
            $this->addStudentsToExcel($sheet, $data['students'], $row);
        } elseif (isset($data['htes'])) {
            $this->addHTEsToExcel($sheet, $data['htes'], $row);
        } else {
            // Generic data
            $sheet->setCellValue('A' . $row, 'Test Data:');
            $row++;
            $sheet->setCellValue('A' . $row, json_encode($data, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Add students data to Excel sheet
     */
    private function addStudentsToExcel($sheet, array $students, int &$row): void
    {
        // Headers
        $headers = ['ID', 'Student Number', 'First Name', 'Last Name', 'Section', 'Specialization', 'Status'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
        }
        $row++;

        // Data
        foreach ($students as $student) {
            $col = 'A';
            $sheet->setCellValue($col . $row, $student['id'] ?? '');
            $col++;
            $sheet->setCellValue($col . $row, $student['student_number'] ?? '');
            $col++;
            $sheet->setCellValue($col . $row, $student['first_name'] ?? '');
            $col++;
            $sheet->setCellValue($col . $row, $student['last_name'] ?? '');
            $col++;
            $sheet->setCellValue($col . $row, $student['section'] ?? '');
            $col++;
            $sheet->setCellValue($col . $row, $student['specialization'] ?? '');
            $col++;
            $sheet->setCellValue($col . $row, ($student['is_active'] ?? false) ? 'Active' : 'Inactive');
            $row++;
        }
    }

    /**
     * Add HTEs data to Excel sheet
     */
    private function addHTEsToExcel($sheet, array $htes, int &$row): void
    {
        // Headers
        $headers = ['ID', 'Company Name', 'Contact Person', 'Email', 'Phone', 'Utilization Rate'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
        }
        $row++;

        // Data
        foreach ($htes as $hte) {
            $col = 'A';
            $sheet->setCellValue($col . $row, $hte['id'] ?? '');
            $col++;
            $sheet->setCellValue($col . $row, $hte['company_name'] ?? '');
            $col++;
            $sheet->setCellValue($col . $row, $hte['contact_person'] ?? '');
            $col++;
            $sheet->setCellValue($col . $row, $hte['email'] ?? '');
            $col++;
            $sheet->setCellValue($col . $row, $hte['phone'] ?? '');
            $col++;
            $sheet->setCellValue($col . $row, ($hte['utilization_rate'] ?? 0) . '%');
            $row++;
        }
    }

    /**
     * Get test sections for dropdown
     */
    private function getTestSections(): array
    {
        return [
            ['id' => 1, 'name' => 'CS-3A'],
            ['id' => 2, 'name' => 'CS-3B'],
            ['id' => 3, 'name' => 'IT-3A'],
            ['id' => 4, 'name' => 'IT-3B'],
        ];
    }

    /**
     * Get test HTEs for dropdown
     */
    private function getTestHTEs(): array
    {
        return [
            ['id' => 1, 'company_name' => 'TechCorp Inc.'],
            ['id' => 2, 'company_name' => 'DataSoft Ltd.'],
            ['id' => 3, 'company_name' => 'WebSolutions Co.'],
            ['id' => 4, 'company_name' => 'CloudTech Systems'],
        ];
    }

    /**
     * Get test internships for dropdown
     */
    private function getTestInternships(): array
    {
        return [
            ['id' => 1, 'position_title' => 'Software Developer Intern', 'hte_id' => 1],
            ['id' => 2, 'position_title' => 'Data Analyst Intern', 'hte_id' => 2],
            ['id' => 3, 'position_title' => 'Web Developer Intern', 'hte_id' => 3],
            ['id' => 4, 'position_title' => 'Cloud Engineer Intern', 'hte_id' => 4],
        ];
    }
}
