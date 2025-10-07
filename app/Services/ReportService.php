<?php

namespace App\Services;

use App\Models\Section;
use App\Models\Student;
use App\Models\HTE;
use App\Models\Internship;
use App\Models\Placement;
use App\Models\Endorsement;
use App\Models\StudentPlacement;
use App\Models\Adviser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    protected array $reportConfigs = [];
    protected array $rolePermissions = [];

    public function __construct()
    {
        $this->loadReportConfigurations();
        $this->loadRolePermissions();
    }

    /**
     * Load report configurations from a centralized config
     */
    protected function loadReportConfigurations(): void
    {
        $this->reportConfigs = [
            'comprehensive' => [
                'name' => 'Comprehensive report',
                'description' => 'Complete system overview with all sections, students, placements, and HTE performance',
                'category' => 'System Overview',
                'icon' => 'GlobeIcon',
                'requires_section' => false,
                'requires_hte' => false,
                'roles' => ['admin'],
                'data_method' => 'getComprehensiveData',
                'template' => 'comprehensive',
                'excel_method' => 'generateComprehensiveExcel',
            ],
            'hte-performance' => [
                'name' => 'HTE Performance report',
                'description' => 'Host Training Establishment performance and utilization rates',
                'category' => 'Performance Analytics',
                'icon' => 'BuildingIcon',
                'requires_section' => false,
                'requires_hte' => false,
                'roles' => ['admin'],
                'data_method' => 'getHTEPerformanceData',
                'template' => 'hte-performance',
                'excel_method' => 'generateHTEPerformanceExcel',
            ],
            'performance-analysis' => [
                'name' => 'Student Performance report',
                'description' => 'Detailed analysis of student performance and rankings',
                'category' => 'Performance Analytics',
                'icon' => 'TrendingUpIcon',
                'requires_section' => true,
                'requires_hte' => false,
                'roles' => ['admin', 'adviser'],
                'data_method' => 'getPerformanceAnalysisData',
                'template' => 'performance-analysis',
                'excel_method' => 'generatePerformanceAnalysisExcel',
                ],
            'hte-profile' => [
                'name' => 'HTE Profile Report',
                'description' => 'Company profile and internship offerings overview',
                'category' => 'HTE Reports',
                'icon' => 'BuildingIcon',
                'requires_section' => false,
                'requires_hte' => true,
                'requires_internship' => false,
                'roles' => ['admin', 'hte'],
                'data_method' => 'getHTEProfileData',
                'template' => 'hte-profile',
                'excel_method' => 'generateHTEProfileExcel',
            ],
            'hte-internship-slots' => [
                'name' => 'Internship Slots report',
                'description' => 'Available internship slots and utilization rates',
                'category' => 'HTE Reports',
                'icon' => 'ClipboardListIcon',
                'requires_section' => false,
                'requires_hte' => false,
                'requires_internship' => true,
                'roles' => ['admin', 'hte'],
                'data_method' => 'getHTEInternshipSlotsData',
                'template' => 'hte-internship-slots',
                'excel_method' => 'generateHTEInternshipSlotsExcel',
            ],
            'hte-placed-students' => [
                'name' => 'HTE Placed Students report',
                'description' => 'Students placed in this HTE with performance metrics',
                'category' => 'HTE Reports',
                'icon' => 'UsersIcon',
                'requires_section' => false,
                'requires_hte' => false,
                'requires_internship' => true,
                'roles' => ['admin', 'hte'],
                'data_method' => 'getHTEPlacedStudentsData',
                'template' => 'hte-placed-students',
                'excel_method' => 'generateHTEPlacedStudentsExcel',
            ],
            'adviser-performance' => [
                'name' => 'Adviser Performance report',
                'description' => 'Comprehensive overview of adviser performance, assigned sections, and student outcomes',
                'category' => 'Adviser Reports',
                'icon' => 'UserIcon',
                'requires_section' => false,
                'requires_hte' => false,
                'roles' => ['admin'],
                'data_method' => 'getAdviserPerformanceData',
                'template' => 'adviser-performance',
                'excel_method' => 'generateAdviserPerformanceExcel',
            ],
            'student-list' => [
                'name' => 'Student List report',
                'description' => 'Complete list of students with basic information',
                'category' => 'Student Reports',
                'icon' => 'UsersIcon',
                'requires_section' => true,
                'requires_hte' => false,
                'roles' => ['admin', 'adviser'],
                'data_method' => 'getStudentListData',
                'template' => 'student-list',
                'excel_method' => 'generateStudentListExcel',
            ],
            'placed-students' => [
                'name' => 'Placed Students report',
                'description' => 'Students who have been successfully placed in internships',
                'category' => 'Placement Reports',
                'icon' => 'TargetIcon',
                'requires_section' => true,
                'requires_hte' => false,
                'roles' => ['admin', 'adviser', 'hte'],
                'data_method' => 'getPlacedStudentsData',
                'template' => 'placed-students',
                'excel_method' => 'generatePlacedStudentsExcel',
            ],
            'endorsed-students' => [
                'name' => 'Endorsed Students report',
                'description' => 'Students who have been endorsed for internship placement',
                'category' => 'Placement Reports',
                'icon' => 'UserCheckIcon',
                'requires_section' => true,
                'requires_hte' => false,
                'roles' => ['admin', 'adviser'],
                'data_method' => 'getEndorsedStudentsData',
                'template' => 'endorsed-students',
                'excel_method' => 'generateEndorsedStudentsExcel',
            ],
            'student-assessment' => [
                'name' => 'Student Assessment report',
                'description' => 'Detailed list of students with assessment status and scores',
                'category' => 'Student Reports',
                'icon' => 'UsersIcon',
                'requires_section' => true,
                'requires_hte' => false,
                'roles' => ['admin', 'adviser'],
                'data_method' => 'getStudentAssessmentData',
                'template' => 'student-assessment',
                'excel_method' => 'generateStudentAssessmentExcel',
            ],         
        ];
    }

    /**
     * Load role-based permissions
     */
    protected function loadRolePermissions(): void
    {
        $this->rolePermissions = [
            'admin' => ['all'], // Admin can access all reports
            'adviser' => ['student-list', 'student-assessment', 'placed-students', 'endorsed-students', 'performance-analysis'],
            'hte' => ['hte-profile', 'hte-internship-slots', 'hte-placed-students'],
        ];
    }

    /**
     * Get available reports for a specific role
     */
    public function getAvailableReports(string $role): array
    {
        $allowedReports = $this->rolePermissions[$role] ?? [];

        if (in_array('all', $allowedReports)) {
            return $this->reportConfigs;
        }

        return array_filter($this->reportConfigs, function ($config, $reportType) use ($allowedReports) {
            return in_array($reportType, $allowedReports) || in_array('all', $allowedReports);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Get report configuration by type
     */
    public function getReportConfig(string $reportType): ?array
    {
        return $this->reportConfigs[$reportType] ?? null;
    }

    /**
     * Check if user role can access specific report
     */
    public function canAccessReport(string $role, string $reportType): bool
    {
        $config = $this->getReportConfig($reportType);
        if (!$config) {
            return false;
        }

        $allowedReports = $this->rolePermissions[$role] ?? [];
        return in_array('all', $allowedReports) || in_array($reportType, $allowedReports);
    }

    /**
     * Get report data based on type and parameters
     */
    public function getReportData(string $reportType, array $params = []): array
    {
        $config = $this->getReportConfig($reportType);
        if (!$config) {
            throw new \InvalidArgumentException("report type '{$reportType}' not found");
        }

        $method = $config['data_method'];
        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException("Method '{$method}' not implemented");
        }

        return $this->$method($params);
    }

    /**
     * Get student list data
     */
    protected function getStudentListData(array $params): array
    {
        $query = Student::with(['user', 'section', 'placements.internship.hte'])
            ->whereHas('user', function ($query) {
                $query->where('status', 'verified');
            });

        if (isset($params['section_id']) && $params['section_id'] !== 'all') {
            $query->where('section_id', $params['section_id']);
        } else if (isset($params['section_id']) && $params['section_id'] === 'all' && isset($params['user_role']) && $params['user_role'] === 'adviser') {
            // For advisers, "all sections" means only their assigned active sections
            $adviserRecord = $params['user']->adviser;
            if ($adviserRecord) {
                $assignedSectionIds = $adviserRecord->sections()->where('status', 'active')->pluck('sections.section_id')->toArray();
                if (empty($assignedSectionIds)) {
                    // If no active sections, return empty collection
                    return [
                        'students' => collect(),
                        'stats' => [
                            ['label' => 'Total Students', 'value' => 0],
                        ],
                        'section_name' => 'All Assigned Sections',
                    ];
                }
                $query->whereIn('section_id', $assignedSectionIds);
            } else {
                // If no adviser record, return empty collection
                return [
                    'students' => collect(),
                    'stats' => [
                        ['label' => 'Total Students', 'value' => 0],
                    ],
                    'section_name' => 'All Assigned Sections',
                ];
            }
        }

        $students = $query->get();

        $stats = [
            ['label' => 'Total Students', 'value' => $students->count()],
        ];

        // Transform students to include phone number
        $studentsWithPhone = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'student_number' => $student->student_number,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'middle_name' => $student->middle_name,
                'phone' => $student->phone,
                'section' => $student->section->section_name ?? 'N/A',
                'is_active' => $student->is_active,
                'is_submit' => $student->is_submit,
                'created_at' => $student->created_at,
                'updated_at' => $student->updated_at,
                'user' => $student->user,
                'section_model' => $student->section,
                'scores' => $student->scores,
                'placements' => $student->placements,
                'endorsements' => $student->endorsements,
            ];
        });

        return [
            'students' => $studentsWithPhone,
            'stats' => $stats,
            'section_name' => $params['section_name'] ?? 'All Sections',
        ];
    }

    /**
     * Get student assessment data
     */
    protected function getStudentAssessmentData(array $params): array
    {
        $query = Student::with(['user', 'section', 'scores', 'placements.internship.hte'])
            ->whereHas('user', function ($query) {
                $query->where('status', 'verified');
            });

        if (isset($params['section_id']) && $params['section_id'] !== 'all') {
            $query->where('section_id', $params['section_id']);
        } else if (isset($params['section_id']) && $params['section_id'] === 'all' && isset($params['user_role']) && $params['user_role'] === 'adviser') {
            // For advisers, "all sections" means only their assigned sections
            $adviserRecord = $params['user']->adviser;
            if ($adviserRecord) {
                $assignedSectionIds = $adviserRecord->sections->pluck('section_id')->toArray();
                $query->whereIn('section_id', $assignedSectionIds);
            } else {
                // If no adviser record, return empty collection
                return [
                    'students' => collect(),
                    'stats' => [
                        ['label' => 'Total Students', 'value' => 0],
                        ['label' => 'Assessed Students', 'value' => 0],
                        ['label' => 'Average Score', 'value' => 'N/A'],
                        ['label' => 'Placement Rate', 'value' => 'N/A'],
                    ],
                    'section_name' => 'All Assigned Sections',
                ];
            }
        }

        $students = $query->get();

        $stats = [
            ['label' => 'Total Students', 'value' => $students->count()],
            ['label' => 'Assessed Students', 'value' => $students->where('is_submit', true)->count()],
            ['label' => 'Unassessed Students', 'value' => $students->where('is_submit', false)->count()],
            ['label' => 'Average Score', 'value' => $students->where('is_submit', true)->map(function($student) {
                return $student->scores ? $student->scores->sum('score') : 0;
            })->avg() ?? 0],
        ];

        return [
            'students' => $students,
            'stats' => $stats,
            'section_name' => $params['section_name'] ?? 'All Sections',
        ];
    }

    /**
     * Get placed students data
     */
    protected function getPlacedStudentsData(array $params): array
    {
        $query = Student::with(['user', 'section', 'placements.internship.hte'])
            ->whereHas('placements')
            ->whereHas('user', function ($query) {
                $query->where('status', 'verified');
            });

        if (isset($params['section_id']) && $params['section_id'] !== 'all') {
            $query->where('section_id', $params['section_id']);
        } else if (isset($params['section_id']) && $params['section_id'] === 'all' && isset($params['user_role']) && $params['user_role'] === 'adviser') {
            // For advisers, "all sections" means only their assigned active sections
            $adviserRecord = $params['user']->adviser;
            if ($adviserRecord) {
                $assignedSectionIds = $adviserRecord->sections()->where('status', 'active')->pluck('sections.section_id')->toArray();
                if (empty($assignedSectionIds)) {
                    // If no active sections, return empty collection
                    return [
                        'students' => collect(),
                        'stats' => [
                            ['label' => 'Total Placed Students', 'value' => 0],
                            ['label' => 'Active Placements', 'value' => 0],
                            ['label' => 'Completed Placements', 'value' => 0],
                        ],
                        'section_name' => 'All Assigned Sections',
                    ];
                }
                $query->whereIn('section_id', $assignedSectionIds);
            } else {
                // If no adviser record, return empty collection
                return [
                    'students' => collect(),
                    'stats' => [
                        ['label' => 'Total Placed Students', 'value' => 0],
                        ['label' => 'Active Placements', 'value' => 0],
                        ['label' => 'Completed Placements', 'value' => 0],
                    ],
                    'section_name' => 'All Assigned Sections',
                ];
            }
        }

        if (isset($params['hte_id'])) {
            $query->whereHas('placements.internship', function ($q) use ($params) {
                $q->where('hte_id', $params['hte_id']);
            });
        }

        $students = $query->get();

        $stats = [
            ['label' => 'Placed Students', 'value' => $students->count()],
            ['label' => 'Active Placements', 'value' => $students->filter(function($student) {
                return $student->placements->where('status', 'approved')->isNotEmpty();
            })->count()],
            ['label' => 'Completed Placements', 'value' => $students->filter(function($student) {
                return $student->placements->where('status', 'completed')->isNotEmpty();
            })->count()],
        ];

        return [
            'students' => $students,
            'stats' => $stats,
            'section_name' => $params['section_name'] ?? 'All Sections',
        ];
    }

    /**
     * Get endorsed students data
     */
    protected function getEndorsedStudentsData(array $params): array
    {
        $query = Student::with(['user', 'section', 'endorsements.internship.hte', 'placements'])
            ->whereHas('endorsements')
            ->whereHas('user', function ($query) {
                $query->where('status', 'verified');
            })
            ->whereDoesntHave('placements', function ($q) {
                $q->where('status', 'approved');
            });

        if (isset($params['section_id']) && $params['section_id'] !== 'all') {
            $query->where('section_id', $params['section_id']);
        } else if (isset($params['section_id']) && $params['section_id'] === 'all' && isset($params['user_role']) && $params['user_role'] === 'adviser') {
            // For advisers, "all sections" means only their assigned active sections
            $adviserRecord = $params['user']->adviser;
            if ($adviserRecord) {
                $assignedSectionIds = $adviserRecord->sections()->where('status', 'active')->pluck('sections.section_id')->toArray();
                if (empty($assignedSectionIds)) {
                    // If no active sections, return empty collection
                    return [
                        'students' => collect(),
                        'stats' => [
                            ['label' => 'Total Endorsements', 'value' => 0],
                            ['label' => 'Pending Endorsements', 'value' => 0],
                            ['label' => 'Approved Endorsements', 'value' => 0],
                            ['label' => 'Rejected Endorsements', 'value' => 0],
                            ['label' => 'Average Compatibility Score', 'value' => 'N/A'],
                        ],
                        'endorsementStats' => [
                            'totalEndorsements' => 0,
                            'pendingEndorsements' => 0,
                            'approvedEndorsements' => 0,
                            'rejectedEndorsements' => 0,
                            'averageCompatibilityScore' => 0,
                        ],
                        'section_name' => 'All Assigned Sections',
                    ];
                }
                $query->whereIn('section_id', $assignedSectionIds);
            } else {
                // If no adviser record, return empty collection
                return [
                    'students' => collect(),
                    'stats' => [
                        ['label' => 'Total Endorsements', 'value' => 0],
                        ['label' => 'Pending Endorsements', 'value' => 0],
                        ['label' => 'Approved Endorsements', 'value' => 0],
                        ['label' => 'Rejected Endorsements', 'value' => 0],
                        ['label' => 'Average Compatibility Score', 'value' => 'N/A'],
                    ],
                    'endorsementStats' => [
                        'totalEndorsements' => 0,
                        'pendingEndorsements' => 0,
                        'approvedEndorsements' => 0,
                        'rejectedEndorsements' => 0,
                        'averageCompatibilityScore' => 0,
                    ],
                    'section_name' => 'All Assigned Sections',
                ];
            }
        }

        $students = $query->get();

        $endorsementStats = [
            'totalEndorsements' => $students->sum(function ($student) {
                return $student->endorsements ? $student->endorsements->count() : 0;
            }),
            'pendingEndorsements' => $students->sum(function ($student) {
                return $student->endorsements ? $student->endorsements->where('status', 'pending')->count() : 0;
            }),
            'approvedEndorsements' => $students->sum(function ($student) {
                return $student->endorsements ? $student->endorsements->where('status', 'approved')->count() : 0;
            }),
            'rejectedEndorsements' => $students->sum(function ($student) {
                return $student->endorsements ? $student->endorsements->where('status', 'rejected')->count() : 0;
            }),
            'averageCompatibilityScore' => $students->avg('compatibility_score') ?? 0,
        ];

        return [
            'students' => $students,
            'stats' => [
                ['label' => 'Total Endorsements', 'value' => $endorsementStats['totalEndorsements']],
                ['label' => 'Pending Endorsements', 'value' => $endorsementStats['pendingEndorsements']],
                ['label' => 'Approved Endorsements', 'value' => $endorsementStats['approvedEndorsements']],
                ['label' => 'Rejected Endorsements', 'value' => $endorsementStats['rejectedEndorsements']],
                ['label' => 'Average Compatibility Score', 'value' => number_format($endorsementStats['averageCompatibilityScore'], 1)],
            ],
            'endorsementStats' => $endorsementStats,
            'section_name' => $params['section_name'] ?? 'All Sections',
        ];
    }

    /**
     * Get performance analysis data
     */
    protected function getPerformanceAnalysisData(array $params): array
    {
        $query = Student::with(['user', 'section', 'scores', 'placements.internship.hte']);

        if (isset($params['section_id']) && $params['section_id'] !== 'all') {
            $query->where('section_id', $params['section_id']);
        } else if (isset($params['section_id']) && $params['section_id'] === 'all' && isset($params['user_role']) && $params['user_role'] === 'adviser') {
            // For advisers, "all sections" means only their assigned active sections
            $adviserRecord = $params['user']->adviser;
            if ($adviserRecord) {
                $assignedSectionIds = $adviserRecord->sections()->where('status', 'active')->pluck('sections.section_id')->toArray();
                if (empty($assignedSectionIds)) {
                    // If no active sections, return empty collection
                    return [
                        'students' => collect(),
                        'stats' => [
                            ['label' => 'Total Students', 'value' => 0],
                            ['label' => 'Assessed Students', 'value' => 0],
                            ['label' => 'Average Score', 'value' => 'N/A'],
                            ['label' => 'Placement Rate', 'value' => 'N/A'],
                        ],
                        'performanceStats' => [
                            'totalStudents' => 0,
                            'assessedStudents' => 0,
                            'averageScore' => 0,
                            'topPerformers' => collect(),
                            'placementRate' => 0,
                        ],
                        'section_name' => 'All Assigned Sections',
                    ];
                }
                $query->whereIn('section_id', $assignedSectionIds);
            } else {
                // If no adviser record, return empty collection
                return [
                    'students' => collect(),
                    'stats' => [
                        ['label' => 'Total Students', 'value' => 0],
                        ['label' => 'Assessed Students', 'value' => 0],
                        ['label' => 'Average Score', 'value' => 'N/A'],
                        ['label' => 'Placement Rate', 'value' => 'N/A'],
                    ],
                    'performanceStats' => [
                        'totalStudents' => 0,
                        'assessedStudents' => 0,
                        'averageScore' => 0,
                        'topPerformers' => collect(),
                        'placementRate' => 0,
                    ],
                    'section_name' => 'All Assigned Sections',
                ];
            }
        }

        $students = $query->get();

        // Count students with placements using the query builder
        $studentsWithPlacementsQuery = Student::when(isset($params['section_id']) && $params['section_id'] !== 'all', 
            function ($q) use ($params) {
                $q->where('section_id', $params['section_id']);
            });
            
        // Apply adviser filtering for "all sections" in the count query too
        if (isset($params['section_id']) && $params['section_id'] === 'all' && isset($params['user_role']) && $params['user_role'] === 'adviser') {
            $adviserRecord = $params['user']->adviser;
            if ($adviserRecord) {
                $assignedSectionIds = $adviserRecord->sections()->where('status', 'active')->pluck('sections.section_id')->toArray();
                if (!empty($assignedSectionIds)) {
                    $studentsWithPlacementsQuery->whereIn('section_id', $assignedSectionIds);
                }
            }
        }
        
        $studentsWithPlacements = $studentsWithPlacementsQuery->whereHas('placements')->count();

        $performanceStats = [
            'totalStudents' => $students->count(),
            'assessedStudents' => $students->where('is_submit', true)->count(),
            'averageScore' => $students->where('is_submit', true)->map(function($student) {
                return $student->scores ? $student->scores->sum('score') : 0;
            })->avg() ?? 0,
            'topPerformers' => $students->where('is_submit', true)->sortByDesc(function($student) {
                return $student->scores ? $student->scores->sum('score') : 0;
            })->take(10),
            'placementRate' => $studentsWithPlacements / max($students->count(), 1) * 100,
        ];

        return [
            'students' => $students,
            'stats' => [
                ['label' => 'Total Students', 'value' => $performanceStats['totalStudents']],
                ['label' => 'Assessed Students', 'value' => $performanceStats['assessedStudents']],
                ['label' => 'Average Score', 'value' => number_format($performanceStats['averageScore'], 1)],
                ['label' => 'Placement Rate', 'value' => number_format($performanceStats['placementRate'], 1) . '%'],
            ],
            'performanceStats' => $performanceStats,
            'section_name' => $params['section_name'] ?? 'All Sections',
        ];
    }

    /**
     * Get HTE performance data
     */
    protected function getHTEPerformanceData(array $params): array
    {
        $htes = HTE::with(['internships.studentPlacements.student', 'internships.studentPlacements.student.section'])->get();

        $hteStats = $htes->map(function ($hte) {
            $totalSlots = $hte->internships->sum('slot_count');
            $usedSlots = $hte->internships->sum(function ($internship) {
                return $internship->studentPlacements->where('status', 'approved')->count();
            });
            $utilizationRate = $totalSlots > 0 ? ($usedSlots / $totalSlots) * 100 : 0;

            return [
                'hte' => $hte,
                'totalSlots' => $totalSlots,
                'usedSlots' => $usedSlots,
                'utilizationRate' => $utilizationRate,
                'averageRating' => $hte->average_rating ?? 0,
            ];
        });

        return [
            'htes' => $htes,
            'stats' => $hteStats->map(function($stat) {
                return [
                    'label' => $stat['hte']->company_name,
                    'value' => number_format($stat['utilizationRate'], 1) . '%',
                ];
            }),
            'hteStats' => $hteStats,
            'totalHTEs' => $htes->count(),
            'averageUtilization' => $hteStats->avg('utilizationRate'),
        ];
    }

    /**
     * Get comprehensive data - General summary of all report types
     */
    protected function getComprehensiveData(array $params): array
    {
        // Get all sections with counts
        $sections = Section::withCount(['students', 'students as active_students_count' => function ($query) {
            $query->where('is_active', true);
        }])->get();

        // Get all students with relationships
        $students = Student::with(['user', 'section', 'scores', 'placements.internship.hte', 'endorsements.internship.hte'])->get();
        
        // Get all HTEs with relationships
        $htes = HTE::with(['internships.studentPlacements.student'])->get();
        
        // Get all placements
        $placements = StudentPlacement::with(['student', 'internship.hte'])->get();
        
        // Get all endorsements
        $endorsements = Endorsement::with(['student', 'internship.hte'])->get();

        // Calculate comprehensive statistics
        $assessedStudents = $students->where('is_submit', true);
        $studentsWithPlacements = Student::whereHas('placements')->count();
        $studentsWithEndorsements = Student::whereHas('endorsements')->count();
        
        // Calculate average scores
        $averageScore = $assessedStudents->map(function($student) {
            return $student->scores ? $student->scores->sum('score') : 0;
        })->avg() ?? 0;

        // Calculate placement statistics
        $approvedPlacements = $placements->where('status', 'approved');
        $pendingPlacements = $placements->where('status', 'pending');
        $rejectedPlacements = $placements->where('status', 'rejected');

        // Calculate endorsement statistics
        $approvedEndorsements = $endorsements->where('status', 'approved');
        $pendingEndorsements = $endorsements->where('status', 'pending');
        $rejectedEndorsements = $endorsements->where('status', 'rejected');

        // Calculate HTE utilization
        $totalSlots = $htes->sum(function($hte) {
            return $hte->internships ? $hte->internships->sum('slot_count') : 0;
        });
        $usedSlots = $approvedPlacements->count();
        $utilizationRate = $totalSlots > 0 ? ($usedSlots / $totalSlots) * 100 : 0;

        // Overall system statistics
        $overallStats = [
            'totalSections' => $sections->count(),
            'totalStudents' => $students->count(),
            'activeStudents' => $students->where('is_active', true)->count(),
            'assessedStudents' => $assessedStudents->count(),
            'unassessedStudents' => $students->where('is_submit', false)->count(),
            'averageScore' => round($averageScore, 2),
            'totalHTEs' => $htes->count(),
            'activeHTEs' => $htes->where('is_active', true)->count(),
            'totalSlots' => $totalSlots,
            'usedSlots' => $usedSlots,
            'availableSlots' => $totalSlots - $usedSlots,
            'utilizationRate' => round($utilizationRate, 2),
            'totalPlacements' => $placements->count(),
            'approvedPlacements' => $approvedPlacements->count(),
            'pendingPlacements' => $pendingPlacements->count(),
            'rejectedPlacements' => $rejectedPlacements->count(),
            'placementRate' => $students->count() > 0 ? round(($studentsWithPlacements / $students->count()) * 100, 2) : 0,
            'totalEndorsements' => $endorsements->count(),
            'approvedEndorsements' => $approvedEndorsements->count(),
            'pendingEndorsements' => $pendingEndorsements->count(),
            'rejectedEndorsements' => $rejectedEndorsements->count(),
            'endorsementRate' => $students->count() > 0 ? round(($studentsWithEndorsements / $students->count()) * 100, 2) : 0,
        ];

        // Top performing sections
        $sectionStats = $sections->map(function($section) use ($students) {
            $sectionStudents = $students->where('section_id', $section->section_id);
            $assessedCount = $sectionStudents->where('is_submit', true)->count();
            $averageScore = $sectionStudents->where('is_submit', true)->map(function($student) {
                return $student->scores ? $student->scores->sum('score') : 0;
            })->avg() ?? 0;
            $placementCount = $sectionStudents->filter(function($student) {
                return $student->placements && $student->placements->isNotEmpty();
            })->count();
            $endorsementCount = $sectionStudents->filter(function($student) {
                return $student->endorsements && $student->endorsements->isNotEmpty();
            })->count();
            
            return [
                'section_name' => $section->section_name,
                'total_students' => $sectionStudents->count(),
                'average_score' => round($averageScore, 2),
                'placement_rate' => $sectionStudents->count() > 0 ? round(($placementCount / $sectionStudents->count()) * 100, 2) : 0,
                'endorsement_rate' => $sectionStudents->count() > 0 ? round(($endorsementCount / $sectionStudents->count()) * 100, 2) : 0,
            ];
        })->sortByDesc('average_score')->take(5);

        // Top performing HTEs
        $htePerformanceStats = $htes->map(function($hte) {
            $internships = $hte->internships ?? collect();
            $totalSlots = $internships->sum('slot_count');
            $usedSlots = $internships->sum(function($internship) {
                return $internship->studentPlacements ? $internship->studentPlacements->where('status', 'approved')->count() : 0;
            });
            $utilizationRate = $totalSlots > 0 ? ($usedSlots / $totalSlots) * 100 : 0;
            
            return [
                'company_name' => $hte->company_name,
                'total_internships' => $internships->count(),
                'total_slots' => $totalSlots,
                'used_slots' => $usedSlots,
                'utilization_rate' => round($utilizationRate, 2),
                'placed_students' => $usedSlots,
            ];
        })->sortByDesc('utilization_rate')->take(5);

        return [
            'sections' => $sections,
            'students' => $students,
            'htes' => $htes,
            'placements' => $placements,
            'endorsements' => $endorsements,
            'overallStats' => [
                ['label' => 'Total Sections', 'value' => $overallStats['totalSections']],
                ['label' => 'Total Students', 'value' => $overallStats['totalStudents']],
                ['label' => 'Active Students', 'value' => $overallStats['activeStudents']],
                ['label' => 'Assessed Students', 'value' => $overallStats['assessedStudents']],
                ['label' => 'Average Score', 'value' => $overallStats['averageScore']],
                ['label' => 'Total HTEs', 'value' => $overallStats['totalHTEs']],
                ['label' => 'Active HTEs', 'value' => $overallStats['activeHTEs']],
                ['label' => 'Total Slots', 'value' => $overallStats['totalSlots']],
                ['label' => 'Used Slots', 'value' => $overallStats['usedSlots']],
                ['label' => 'Utilization Rate', 'value' => $overallStats['utilizationRate'] . '%'],
                ['label' => 'Placement Rate', 'value' => $overallStats['placementRate'] . '%'],
                ['label' => 'Endorsement Rate', 'value' => $overallStats['endorsementRate'] . '%'],
            ],
            'studentStats' => [
                ['label' => 'Total Students', 'value' => $overallStats['totalStudents']],
                ['label' => 'Active Students', 'value' => $overallStats['activeStudents']],
                ['label' => 'Assessed Students', 'value' => $overallStats['assessedStudents']],
                ['label' => 'Average Score', 'value' => $overallStats['averageScore']],
                ['label' => 'Placement Rate', 'value' => $overallStats['placementRate'] . '%'],
                ['label' => 'Endorsement Rate', 'value' => $overallStats['endorsementRate'] . '%'],
            ],
            'hteStats' => [
                ['label' => 'Total HTEs', 'value' => $overallStats['totalHTEs']],
                ['label' => 'Active HTEs', 'value' => $overallStats['activeHTEs']],
                ['label' => 'Total Slots', 'value' => $overallStats['totalSlots']],
                ['label' => 'Used Slots', 'value' => $overallStats['usedSlots']],
                ['label' => 'Available Slots', 'value' => $overallStats['availableSlots']],
                ['label' => 'Utilization Rate', 'value' => $overallStats['utilizationRate'] . '%'],
            ],
            'placementStats' => [
                ['label' => 'Total Placements', 'value' => $overallStats['totalPlacements']],
                ['label' => 'Approved', 'value' => $overallStats['approvedPlacements']],
                ['label' => 'Pending', 'value' => $overallStats['pendingPlacements']],
                ['label' => 'Rejected', 'value' => $overallStats['rejectedPlacements']],
            ],
            'endorsementStats' => [
                ['label' => 'Total Endorsements', 'value' => $overallStats['totalEndorsements']],
                ['label' => 'Approved', 'value' => $overallStats['approvedEndorsements']],
                ['label' => 'Pending', 'value' => $overallStats['pendingEndorsements']],
                ['label' => 'Rejected', 'value' => $overallStats['rejectedEndorsements']],
            ],
            'sectionStats' => $sectionStats,
            'htePerformanceStats' => $htePerformanceStats,
        ];
    }

    /**
     * Get adviser performance data - General overview of adviser performance
     */
    protected function getAdviserPerformanceData(array $params): array
    {
        // Get all advisers with their sections and students
        $advisers = Adviser::with([
            'user',
            'sections.students.user',
            'sections.students.scores',
            'sections.students.placements.internship.hte',
            'sections.students.endorsements.internship.hte'
        ])->get();

        // Calculate overall statistics
        $totalAdvisers = $advisers->count();
        $activeAdvisers = $advisers->where('is_active', true)->count();
        $totalSections = $advisers->sum(function($adviser) {
            return $adviser->sections->count();
        });

        // Calculate adviser performance metrics
        $adviserStats = $advisers->map(function($adviser) {
            $sections = $adviser->sections;
            $allStudents = $sections->flatMap(function($section) {
                return $section->students;
            });

            $totalStudents = $allStudents->count();
            $activeStudents = $allStudents->where('is_active', true)->count();
            $assessedStudents = $allStudents->where('is_submit', true)->count();
            
            // Calculate average score for this adviser's students
            $averageScore = $allStudents->where('is_submit', true)->map(function($student) {
                return $student->scores ? $student->scores->sum('score') : 0;
            })->avg() ?? 0;

            // Calculate placement statistics
            $placedStudents = $allStudents->filter(function($student) {
                return $student->placements && $student->placements->isNotEmpty();
            })->count();

            $placementRate = $totalStudents > 0 ? ($placedStudents / $totalStudents) * 100 : 0;

            // Calculate endorsement statistics
            $endorsedStudents = $allStudents->filter(function($student) {
                return $student->endorsements && $student->endorsements->isNotEmpty();
            })->count();

            $endorsementRate = $totalStudents > 0 ? ($endorsedStudents / $totalStudents) * 100 : 0;

            return [
                'adviser' => $adviser,
                'totalSections' => $sections->count(),
                'totalStudents' => $totalStudents,
                'activeStudents' => $activeStudents,
                'assessedStudents' => $assessedStudents,
                'averageScore' => round($averageScore, 2),
                'placedStudents' => $placedStudents,
                'placementRate' => round($placementRate, 2),
                'endorsedStudents' => $endorsedStudents,
                'endorsementRate' => round($endorsementRate, 2),
            ];
        })->sortByDesc('averageScore');

        // Calculate section-wise performance for each adviser
        $adviserSectionStats = $advisers->map(function($adviser) {
            return $adviser->sections->map(function($section) {
                $students = $section->students;
                $assessedStudents = $students->where('is_submit', true);
                $averageScore = $assessedStudents->map(function($student) {
                    return $student->scores ? $student->scores->sum('score') : 0;
                })->avg() ?? 0;

                $placedStudents = $students->filter(function($student) {
                    return $student->placements && $student->placements->isNotEmpty();
                })->count();

                return [
                    'section' => $section,
                    'totalStudents' => $students->count(),
                    'assessedStudents' => $assessedStudents->count(),
                    'averageScore' => round($averageScore, 2),
                    'placedStudents' => $placedStudents,
                    'placementRate' => $students->count() > 0 ? round(($placedStudents / $students->count()) * 100, 2) : 0,
                ];
            });
        });

        // Overall system statistics
        $overallStats = [
            'totalAdvisers' => $totalAdvisers,
            'activeAdvisers' => $activeAdvisers,
            'totalSections' => $totalSections,
            'averageSectionsPerAdviser' => $totalAdvisers > 0 ? round($totalSections / $totalAdvisers, 2) : 0,
            'topPerformingAdviser' => $adviserStats->first(),
            'averagePlacementRate' => $adviserStats->avg('placementRate'),
            'averageEndorsementRate' => $adviserStats->avg('endorsementRate'),
            'averageScore' => $adviserStats->avg('averageScore'),
        ];

        return [
            'advisers' => $advisers,
            'adviserStats' => $adviserStats,
            'adviserSectionStats' => $adviserSectionStats,
            'overallStats' => $overallStats,
        ];
    }

    /**
     * Get HTE profile data
     */
    protected function getHTEProfileData(array $params): array
    {
        $hteId = $params['hte_id'] ?? null;
        
        if (!$hteId) {
            throw new \InvalidArgumentException('HTE ID is required for HTE profile report');
        }

        $hte = HTE::with([
            'internships.studentPlacements.student.user',
            'internships.studentPlacements.student.section'
        ])->findOrFail($hteId);

        // Calculate internship statistics
        $totalInternships = $hte->internships->count();
        $totalSlots = $hte->internships->sum('slot_count');
        $usedSlots = $hte->internships->sum(function ($internship) {
            return $internship->studentPlacements->where('status', 'approved')->count();
        });
        $utilizationRate = $totalSlots > 0 ? ($usedSlots / $totalSlots) * 100 : 0;

        // Get all placed students across all internships
        $placedStudents = collect();
        foreach ($hte->internships as $internship) {
            $placedStudents = $placedStudents->merge(
                $internship->studentPlacements->where('status', 'approved')->map(function ($sp) {
                    return $sp->student;
                })
            );
        }

        // Calculate department distribution
        $departmentStats = $hte->internships->groupBy('department')->map(function ($internships, $department) {
            $totalSlots = $internships->sum('slot_count');
            $usedSlots = $internships->sum(function ($internship) {
                return $internship->studentPlacements->where('status', 'approved')->count();
            });
            
            return [
                'department' => $department,
                'internships_count' => $internships->count(),
                'total_slots' => $totalSlots,
                'used_slots' => $usedSlots,
                'utilization_rate' => $totalSlots > 0 ? ($usedSlots / $totalSlots) * 100 : 0,
            ];
        });

        $stats = [
            ['label' => 'Total Internships', 'value' => $totalInternships],
            ['label' => 'Total Slots Available', 'value' => $totalSlots],
            ['label' => 'Slots Utilized', 'value' => $usedSlots],
            ['label' => 'Utilization Rate', 'value' => number_format($utilizationRate, 1) . '%'],
            ['label' => 'Total Placed Students', 'value' => $placedStudents->count()],
            ['label' => 'Active Departments', 'value' => $departmentStats->count()],
        ];

        return [
            'hte' => $hte,
            'internships' => $hte->internships,
            'placedStudents' => $placedStudents,
            'departmentStats' => $departmentStats,
            'stats' => $stats,
            'totalInternships' => $totalInternships,
            'totalSlots' => $totalSlots,
            'usedSlots' => $usedSlots,
            'utilizationRate' => $utilizationRate,
        ];
    }

    /**
     * Get HTE-specific internship slots data
     */
    protected function getHTEInternshipSlotsData(array $params): array
    {
        $hteId = $params['hte_id'] ?? null;
        $internshipId = $params['internship_id'] ?? null;
        
        // Handle admin users selecting "all internships"
        if ($hteId === 'all') {
            // Admin users can see all internships across all HTEs
            $query = Internship::with(['hte', 'studentPlacements.student']);
            
            // Filter by specific internship if selected
            if ($internshipId && $internshipId !== 'all') {
                $query->where('id', $internshipId);
            }
            
            $internships = $query->get();
            $hte = null; // No specific HTE for admin view
        } else {
            // HTE users or admin users with specific HTE
            if (!$hteId) {
                throw new \InvalidArgumentException('HTE ID is required for HTE-specific reports');
            }
            
            $hte = HTE::with(['internships.studentPlacements.student'])->findOrFail($hteId);
            
            // Filter internships if specific internship is selected
            $internships = $hte->internships;
            if ($internshipId && $internshipId !== 'all') {
                $internships = $internships->where('id', $internshipId);
            }
        }

        $slotStats = $internships->map(function ($internship) {
            $usedSlots = $internship->studentPlacements->where('status', 'approved')->count();
            $utilizationRate = $internship->slot_count > 0 ?
                ($usedSlots / $internship->slot_count) * 100 : 0;

            return [
                'internship' => $internship,
                'usedSlots' => $usedSlots,
                'utilizationRate' => $utilizationRate,
            ];
        });

        return [
            'hte' => $hte,
            'slotStats' => $slotStats,
            'totalSlots' => $internships->sum('slot_count'),
            'usedSlots' => $internships->sum(function ($internship) {
                return $internship->studentPlacements->where('status', 'approved')->count();
            }),
            'internship_name' => $params['internship_name'] ?? 'All Internships',
        ];
    }

    /**
     * Get HTE-specific placed students data
     */
    protected function getHTEPlacedStudentsData(array $params): array
    {
        $hteId = $params['hte_id'] ?? null;
        $internshipId = $params['internship_id'] ?? null;
        
        // Handle admin users selecting "all internships"
        if ($hteId === 'all') {
            // Admin users can see all placed students across all HTEs
            $query = Internship::with(['hte', 'studentPlacements.student.user', 'studentPlacements.student.section']);
            
            // Filter by specific internship if selected
            if ($internshipId && $internshipId !== 'all') {
                $query->where('id', $internshipId);
            }
            
            $internships = $query->get();
            $hte = null; // No specific HTE for admin view
        } else {
            // HTE users or admin users with specific HTE
            if (!$hteId) {
                throw new \InvalidArgumentException('HTE ID is required for HTE-specific reports');
            }
            
            $hte = HTE::with(['internships.studentPlacements.student.user', 'internships.studentPlacements.student.section'])->findOrFail($hteId);
            
            // Filter internships if specific internship is selected
            $internships = $hte->internships;
            if ($internshipId && $internshipId !== 'all') {
                $internships = $internships->where('id', $internshipId);
            }
        }

        $placedStudents = collect();
        foreach ($internships as $internship) {
            $placedStudents = $placedStudents->merge($internship->studentPlacements->map(function ($sp) {
                return $sp->student;
            }));
        }

        $stats = [
            ['label' => 'Total Placed Students', 'value' => $placedStudents->count()],
            ['label' => 'Active Placements', 'value' => $placedStudents->filter(function($student) {
                return $student->placements && $student->placements->where('status', 'approved')->isNotEmpty();
            })->count()],
            ['label' => 'Completed Placements', 'value' => $placedStudents->filter(function($student) {
                return $student->placements && $student->placements->where('status', 'completed')->isNotEmpty();
            })->count()],
        ];

        return [
            'hte' => $hte,
            'students' => $placedStudents,
            'stats' => $stats,
            'internship_name' => $params['internship_name'] ?? 'All Internships',
        ];
    }

    /**
     * Get sections for report filtering
     */
    public function getSections($user = null): Collection
    {
        $query = Section::select('section_id', 'section_name')->where('status', 'active');
        
        // For adviser users, only return sections they're assigned to
        if ($user && $user->getRoleNames()->first() === 'adviser') {
            $adviserRecord = $user->adviser;
            if ($adviserRecord) {
                $query->whereHas('advisers', function ($q) use ($adviserRecord) {
                    $q->where('adviser_id', $adviserRecord->id);
                });
            } else {
                // If adviser user doesn't have an adviser record, return empty collection
                return collect();
            }
        }
        
        return $query->get();
    }

    /**
     * Get HTEs for report filtering
     */
    public function getHTEs($user = null): Collection
    {
        $query = HTE::select('id', 'company_name');
        
        // For HTE users, only return their own HTE
        if ($user && $user->getRoleNames()->first() === 'hte') {
            $hteRecord = $user->hte;
            if ($hteRecord) {
                $query->where('id', $hteRecord->id);
            } else {
                // If HTE user doesn't have an HTE record, return empty collection
                return collect();
            }
        }
        
        return $query->get();
    }

    /**
     * Get internships for report filtering
     */
    public function getInternships($user = null): Collection
    {
        $query = Internship::select('id', 'position_title', 'department', 'hte_id')
            ->with('hte:id,company_name');
        
        // For HTE users, only return their own internships
        if ($user && $user->getRoleNames()->first() === 'hte') {
            $hteRecord = $user->hte;
            if ($hteRecord) {
                $query->where('hte_id', $hteRecord->id);
            } else {
                // If HTE user doesn't have an HTE record, return empty collection
                return collect();
            }
        }
        
        $internships = $query->get();
        
        return $internships;
    }

    /**
     * Get report categories grouped by category
     */
    public function getReportCategories(string $role): array
    {
        $reports = $this->getAvailableReports($role);

        $categories = [];
        foreach ($reports as $type => $config) {
            $category = $config['category'];
            if (!isset($categories[$category])) {
                $categories[$category] = [];
            }
            $categories[$category][] = [
                'type' => $type,
                'name' => $config['name'],
                'description' => $config['description'],
                'icon' => $config['icon'],
                'requires_section' => $config['requires_section'],
                'requires_hte' => $config['requires_hte'],
                'requires_internship' => $config['requires_internship'] ?? false,
            ];
        }

        // Convert to array format expected by React component
        $formattedCategories = [];
        foreach ($categories as $categoryName => $reports) {
            $formattedCategories[] = [
                'title' => $categoryName,
                'reports' => $reports,
            ];
        }

        return $formattedCategories;
    }
}
