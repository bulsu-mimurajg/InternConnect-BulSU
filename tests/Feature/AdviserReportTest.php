<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Section;
use App\Models\AcademeAccount;
use App\Models\Adviser;
use App\Models\HTE;
use App\Models\Internship;
use App\Models\Endorsement;
use App\Models\StudentPlacement;
use App\Models\StudentMatch;
use App\Models\StudentScore;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdviserReportTest extends TestCase
{
    use RefreshDatabase;

    protected $adviser;
    protected $section;
    protected $students = [];
    protected $internship;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test section
        $this->section = Section::create([
            'section_name' => 'BSIT-4A',
            'status' => 'active'
        ]);

        // Create test adviser
        $adviserUser = User::create([
            'username' => 'test_adviser',
            'email' => 'test_adviser@example.com',
            'password' => Hash::make('password'),
            'status' => 'verified'
        ]);
        $adviserUser->assignRole('adviser');

        $this->adviser = Adviser::create([
            'user_id' => $adviserUser->id,
            'adviser_fname' => 'Test',
            'adviser_lname' => 'Adviser',
            'is_active' => true
        ]);

        // Attach section to adviser
        $this->adviser->sections()->attach($this->section->section_id);

        // Create test students
        $this->createTestStudents();

        // Create test internship
        $this->createTestInternship();

        // Create test data
        $this->createTestEndorsementsAndPlacements();
    }

    private function createTestStudents()
    {
        $studentData = [
            ['John', 'Doe', '2021-0001'],
            ['Jane', 'Smith', '2021-0002'],
            ['Bob', 'Johnson', '2021-0003']
        ];

        foreach ($studentData as $index => $data) {
            $user = User::create([
                'username' => 'student_' . ($index + 1),
                'email' => 'student' . ($index + 1) . '@example.com',
                'password' => Hash::make('password'),
                'status' => 'verified'
            ]);
            $user->assignRole('student');

            $academeAccount = AcademeAccount::create([
                'user_id' => $user->id,
                'section_id' => $this->section->section_id
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'student_number' => $data[2],
                'first_name' => $data[0],
                'last_name' => $data[1],
                'middle_name' => 'A',
                'phone' => '0912345678' . $index,
                'section_id' => $this->section->section_id,
                'specialization' => 'Computer Science',
                'address' => 'Test Address ' . ($index + 1),
                'birth_date' => '2000-01-0' . ($index + 1),
                'is_submit' => true,
                'is_placed' => $index === 0, // First student is placed
                'is_active' => true
            ]);

            $this->students[] = $student;
        }
    }

    private function createTestInternship()
    {
        $hteUser = User::create([
            'username' => 'test_hte',
            'email' => 'test_hte@example.com',
            'password' => Hash::make('password'),
            'status' => 'verified'
        ]);
        $hteUser->assignRole('hte');

        $hte = HTE::create([
            'user_id' => $hteUser->id,
            'company_name' => 'Test Company Inc.',
            'contact_person' => 'Test Contact',
            'contact_number' => '09123456788',
            'email' => 'test_hte@example.com',
            'address' => 'Test Company Address',
            'is_submit' => true,
            'is_active' => true
        ]);

        $this->internship = Internship::create([
            'hte_id' => $hte->id,
            'position_title' => 'Software Development Intern',
            'department' => 'IT Department',
            'placement_description' => 'Test internship position',
            'slot_count' => 5,
            'is_active' => true
        ]);
    }

    private function createTestEndorsementsAndPlacements()
    {
        // Create student matches
        foreach ($this->students as $index => $student) {
            StudentMatch::create([
                'student_id' => $student->id,
                'internship_id' => $this->internship->id,
                'rank' => $index + 1,
                'compatibility_score' => 85 + ($index * 5), // 85, 90, 95
                'endorsement_status' => $index < 2 ? 'endorsed' : 'pending',
                'placement_status' => $index === 0 ? 'approved' : 'pending'
            ]);
        }

        // Create endorsements for first 2 students
        foreach (array_slice($this->students, 0, 2) as $index => $student) {
            Endorsement::create([
                'student_id' => $student->id,
                'internship_id' => $this->internship->id,
                'status' => 'endorsed',
                'compatibility_score' => 85 + ($index * 5),
                'notes' => "Excellent candidate for software development role",
                'endorsement_date' => now()->subDays($index + 1)
            ]);
        }

        // Create placement for first student
        StudentPlacement::create([
            'student_id' => $this->students[0]->id,
            'internship_id' => $this->internship->id,
            'status' => 'approved',
            'compatibility_score' => 85,
            'placement_date' => now()->subDays(1)
        ]);
    }

    /** @test */
    public function adviser_can_access_reports_page()
    {
        $adviserUser = $this->adviser->user;
        
        $response = $this->actingAs($adviserUser)
            ->get(route('adviser.reports'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('adviser/report')
                ->has('adviserSection')
                ->has('adviserSections')
                ->has('currentSectionId')
        );
    }

    /** @test */
    public function endorsed_students_pdf_report_generates_successfully()
    {
        $adviserUser = $this->adviser->user;
        
        $response = $this->actingAs($adviserUser)
            ->get(route('adviser.report.export.pdf', ['reportType' => 'endorsed-students']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', function ($value) {
            return str_contains($value, 'endorsed-students-report-');
        });
    }

    /** @test */
    public function placed_students_pdf_report_generates_successfully()
    {
        $adviserUser = $this->adviser->user;
        
        $response = $this->actingAs($adviserUser)
            ->get(route('adviser.report.export.pdf', ['reportType' => 'placed-students']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', function ($value) {
            return str_contains($value, 'placed-students-report-');
        });
    }

    /** @test */
    public function endorsed_students_csv_report_generates_successfully()
    {
        $adviserUser = $this->adviser->user;
        
        $response = $this->actingAs($adviserUser)
            ->get(route('adviser.report.export.csv', ['reportType' => 'endorsed-students']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition', function ($value) {
            return str_contains($value, 'endorsed-students-report-');
        });

        // Check CSV content
        $content = $response->getContent();
        $this->assertStringContainsString('Endorsed Students Report', $content);
        $this->assertStringContainsString('John Doe', $content);
        $this->assertStringContainsString('Jane Smith', $content);
        $this->assertStringContainsString('Test Company Inc.', $content);
    }

    /** @test */
    public function placed_students_csv_report_generates_successfully()
    {
        $adviserUser = $this->adviser->user;
        
        $response = $this->actingAs($adviserUser)
            ->get(route('adviser.report.export.csv', ['reportType' => 'placed-students']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition', function ($value) {
            return str_contains($value, 'placed-students-report-');
        });

        // Check CSV content
        $content = $response->getContent();
        $this->assertStringContainsString('Placed Students Report', $content);
        $this->assertStringContainsString('John Doe', $content);
        $this->assertStringContainsString('Test Company Inc.', $content);
    }

    /** @test */
    public function endorsed_students_report_contains_correct_data()
    {
        $adviserUser = $this->adviser->user;
        
        $response = $this->actingAs($adviserUser)
            ->get(route('adviser.report.export.csv', ['reportType' => 'endorsed-students']));

        $content = $response->getContent();
        
        // Check summary statistics
        $this->assertStringContainsString('Total Students,3', $content);
        $this->assertStringContainsString('Endorsed Students,2', $content);
        $this->assertStringContainsString('Endorsement Rate,66.7%', $content);
        
        // Check student details
        $this->assertStringContainsString('2021-0001,John Doe,BSIT-4A', $content);
        $this->assertStringContainsString('2021-0002,Jane Smith,BSIT-4A', $content);
        $this->assertStringContainsString('Test Company Inc.', $content);
        $this->assertStringContainsString('Software Development Intern', $content);
    }

    /** @test */
    public function placed_students_report_contains_correct_data()
    {
        $adviserUser = $this->adviser->user;
        
        $response = $this->actingAs($adviserUser)
            ->get(route('adviser.report.export.csv', ['reportType' => 'placed-students']));

        $content = $response->getContent();
        
        // Check summary statistics
        $this->assertStringContainsString('Total Students,3', $content);
        $this->assertStringContainsString('Placed Students,1', $content);
        $this->assertStringContainsString('Placement Rate,33.3%', $content);
        
        // Check student details
        $this->assertStringContainsString('2021-0001,John Doe,BSIT-4A', $content);
        $this->assertStringContainsString('Test Company Inc.', $content);
        $this->assertStringContainsString('Software Development Intern', $content);
    }

    /** @test */
    public function invalid_report_type_returns_404()
    {
        $adviserUser = $this->adviser->user;
        
        $response = $this->actingAs($adviserUser)
            ->get(route('adviser.report.export.pdf', ['reportType' => 'invalid-type']));

        $response->assertStatus(404);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_reports()
    {
        $response = $this->get(route('adviser.reports'));
        $response->assertRedirect('/login');
    }

    /** @test */
    public function non_adviser_user_cannot_access_reports()
    {
        $studentUser = $this->students[0]->user;
        
        $response = $this->actingAs($studentUser)
            ->get(route('adviser.reports'));

        $response->assertStatus(403);
    }

    /** @test */
    public function adviser_without_section_gets_empty_reports()
    {
        // Create adviser without sections
        $adviserUser = User::create([
            'username' => 'no_section_adviser',
            'email' => 'no_section@example.com',
            'password' => Hash::make('password'),
            'status' => 'verified'
        ]);
        $adviserUser->assignRole('adviser');

        $adviser = Adviser::create([
            'user_id' => $adviserUser->id,
            'adviser_fname' => 'No',
            'adviser_lname' => 'Section',
            'is_active' => true
        ]);

        $response = $this->actingAs($adviserUser)
            ->get(route('adviser.reports'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->where('adviserSection', null)
                ->where('adviserSections', [])
                ->where('currentSectionId', null)
        );
    }
}
