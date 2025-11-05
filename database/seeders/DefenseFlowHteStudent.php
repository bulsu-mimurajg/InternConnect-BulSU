<?php

namespace Database\Seeders;

use App\Models\Adviser;
use App\Models\Category;
use App\Models\HTE;
use App\Models\Internship;
use App\Models\InternshipSeason;
use App\Models\Section;
use App\Models\Student;
use App\Models\QuestionImportanceRating;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefenseFlowHteStudent extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles
        $this->call(RolePermissionSeeder::class);

        // SIP Coordinator
        User::factory()->admin()->create([
            'username' => 'faye',
            'email' => 'faye@example.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        // Sections
        $sections = [
            '4E-G1', '4E-G2',
        ];

        foreach ($sections as $sectionName) {
            Section::create([
                'section_name' => $sectionName,
                'status' => 'active'
            ]);
        }

        // Adviser
        User::factory()->adviser()->create([
            'username' => 'emman',
            'email' => 'emman@example.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        User::factory()->adviser()->create([
            'username' => 'juana',
            'email' => 'juana@example.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        $emmanUser = User::where('username', 'emman')->first();
        $juanaUser = User::where('username', 'juana')->first();

        $sections = Section::where('status', 'active')->get();

        $advisers = [
            [
                'adviser_fname' => 'Emmanuel',
                'adviser_lname' => 'Santos',
                'is_active' => true,
                'section_ids' => [$sections->first()->section_id ?? 1], // Single section
                'user_id' => $emmanUser->id ?? 2,
            ],
            [
                'adviser_fname' => 'Juana',
                'adviser_lname' => 'Cruz',
                'is_active' => true,
                'section_ids' => [$sections->last()->section_id ?? 2], // Single section
                'user_id' => $juanaUser->id ?? 3,
            ],
        ];

        foreach ($advisers as $adviserData) {
            // Extract section_ids before creating adviser
            $sectionIds = $adviserData['section_ids'];
            unset($adviserData['section_ids']);

            // If no user_id is provided, create a new user for this adviser
            if (!$adviserData['user_id']) {
                $user = User::create([
                    'username' => strtolower($adviserData['adviser_fname'] . '.' . $adviserData['adviser_lname']),
                    'email' => strtolower($adviserData['adviser_fname'] . '.' . $adviserData['adviser_lname']) . '@example.com',
                    'password' => bcrypt('password'),
                    'status' => 'verified',
                ]);

                // Assign adviser role to the user
                $user->assignRole('adviser');

                $adviserData['user_id'] = $user->id;
            }

            // Create the adviser
            $adviser = Adviser::create($adviserData);

            // Attach sections to the adviser
            $adviser->sections()->attach($sectionIds);
        }

        // Student Assessment
        $this->call(
            CategorySeeder::class,
        );

        // HTE
        $hteUsers = [
            [
                'username' => 'maria',
                'email' => 'maria@example.com',
                'status' => 'verified',
                'password' => bcrypt('password'),
            ],
            [
                'username' => 'clara',
                'email' => 'clara@example.com',
                'status' => 'verified',
                'password' => bcrypt('password'),
            ],
        ];

        foreach ($hteUsers as $userData) {
            $user = User::firstOrCreate(
                ['username' => $userData['username']],
                $userData
            );
            if (!$user->hasRole('hte')) {
                $user->assignRole('hte');
            }
        }

        $hteData = [
            [
                'user_id' => User::where('username', 'maria')->first()->id,
                'company_name' => 'MariaTech Solutions',
                'company_address' => '888 Innovation Plaza, Tech Hub District, Manila 1000',
                'company_email' => 'maria@mariatech.com',
                'cperson_fname' => 'George',
                'cperson_lname' => 'Kusunoki',
                'cperson_position' => 'HR Director',
                'cperson_contactnum' => '+63-917-123-4567',
                'is_active' => true,
                'is_submit' => true,
            ],
            [
                'user_id' => User::where('username', 'clara')->first()->id,
                'company_name' => 'TechCorp Solutions',
                'company_address' => '123 Innovation Drive, Tech City, TC 12345',
                'company_email' => 'hr@techcorp.com',
                'cperson_fname' => 'Juan',
                'cperson_lname' => 'Aladdin',
                'cperson_position' => 'HR Manager',
                'cperson_contactnum' => '+1-555-0101',
                'is_active' => true,
                'is_submit' => true,
            ],
        ];

        foreach ($hteData as $data) {
            HTE::firstOrCreate(
                ['user_id' => $data['user_id']],
                $data
            );
        }

        // Internships
        $htes = HTE::all();

        $internshipData = [
            [
                'hte_id' => $htes->where('company_name', 'MariaTech Solutions')->first()->id,
                'position_title' => 'Full-Stack Development Intern',
                'department' => 'Information Technology',
                'placement_description' => 'Work on both frontend and backend development using modern frameworks like React, Laravel, and Node.js. You will participate in the complete software development lifecycle and work on real client projects.',
                'slot_count' => 1,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->where('company_name', 'MariaTech Solutions')->first()->id,
                'position_title' => 'UI/UX Design Intern',
                'department' => 'Design',
                'placement_description' => 'Create user-friendly interfaces and experiences for web and mobile applications. You will work with our design team using tools like Figma, Adobe XD, and conduct user research.',
                'slot_count' => 2,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->where('company_name', 'TechCorp Solutions')->first()->id,
                'position_title' => 'Software Development Intern',
                'department' => 'Information Technology',
                'placement_description' => 'Join our development team and work on real-world projects using modern technologies like React, Node.js, and Python. You will participate in code reviews, attend team meetings, and contribute to our product development process.',
                'slot_count' => 2,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->where('company_name', 'TechCorp Solutions')->first()->id,
                'position_title' => 'Data Science Intern',
                'department' => 'Information Technology',
                'placement_description' => 'Work with our data science team to analyze large datasets, build predictive models, and create data visualizations. Experience with Python, SQL, and machine learning frameworks is preferred.',
                'slot_count' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($internshipData as $data) {
            Internship::create($data);
        }

        // Internship Criteria - Create question importance ratings for each internship
        // Use HTE questions (rating type) that are linked from student questions
        $categories = Category::with(['subCategories.questions' => function($query) {
            $query->where('is_active', true)
                  ->where('question_type', 'rating')
                  ->whereHas('studentQuestion'); // Only HTE questions that have linked student questions
        }])->get();
        $internships = Internship::with('hte')->get();

        foreach ($internships as $internship) {
            $this->command->info("Assigning question importance ratings for internship: {$internship->position_title}");

            if (!$internship->hte) {
                $this->command->warn("  Internship has no HTE. Skipping.");
                continue;
            }

            $totalQuestions = 0;

            foreach ($categories as $category) {
                $subcategories = $category->subCategories;
                
                if ($subcategories->isEmpty()) {
                    $this->command->warn("  Category '{$category->category_name}' has no subcategories. Skipping.");
                    continue;
                }

                foreach ($subcategories as $subcategory) {
                    $questions = $subcategory->questions ?? collect();
                    
                    if ($questions->isEmpty()) {
                        continue;
                    }

                    // Ensure we're working with unique HTE questions (by ID)
                    // Each question should have a unique HTE question
                    $questions = $questions->unique('id')->values();
                    
                    if ($questions->isEmpty()) {
                        continue;
                    }

                    // Ensure each question in this subcategory gets a different rating
                    // Generate unique ratings when possible (≤5 questions), otherwise maximize variety
                    $questionCount = $questions->count();
                    $availableRatings = range(1, 5); // Full range 1-5
                    
                    // If we have 5 or fewer questions, each gets a unique rating (1-5)
                    // If we have more than 5 questions, cycle through ratings but shuffle for variety
                    if ($questionCount <= 5) {
                        // Perfect case: we can assign unique ratings (1-5) to each question
                        shuffle($availableRatings); // Shuffle in place
                        $ratings = array_slice($availableRatings, 0, $questionCount);
                    } else {
                        // More than 5 questions: use full range and repeat, but ensure variety
                        $ratings = [];
                        $baseRatings = [1, 2, 3, 4, 5];
                        shuffle($baseRatings); // Shuffle the base ratings first
                        for ($i = 0; $i < $questionCount; $i++) {
                            // Cycle through shuffled ratings
                            $ratings[] = $baseRatings[$i % 5];
                        }
                        shuffle($ratings); // Randomize the final order
                    }
                    $questionIndex = 0;
                    foreach ($questions as $question) {
                        $rating = $ratings[$questionIndex];
                        
                        QuestionImportanceRating::updateOrCreate(
                            [
                                'hte_id' => $internship->hte->id,
                                'internship_id' => $internship->id,
                                'question_id' => $question->id,
                            ],
                            [
                                'rating' => $rating,
                            ]
                        );
                        
                        $questionIndex++;
                        $totalQuestions++;
                    }
                }
            }

            $this->command->info("  Created {$totalQuestions} question ratings for internship: {$internship->position_title}");
        }

        $this->command->info("Completed assigning question importance ratings.");

        // Students

        $availableSections = Section::whereIn('section_name', ['4E-G1', '4E-G2'])->get();

        $season = $this->getOrCreateSeason();

        $students = [
            // Students with submitted assessments (5 total)
            [
                'student_number' => '2022100101',
                'first_name' => 'John',
                'middle_name' => 'Michael',
                'last_name' => 'Smith',
                'phone' => '09123456789',
                'section_name' => '4E-G1',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100102',
                'first_name' => 'Sarah',
                'middle_name' => 'Jane',
                'last_name' => 'Johnson',
                'phone' => '09123456790',
                'section_name' => '4E-G1',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100103',
                'first_name' => 'Michael',
                'middle_name' => 'David',
                'last_name' => 'Brown',
                'phone' => '09123456791',
                'section_name' => '4E-G2',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100104',
                'first_name' => 'Emily',
                'middle_name' => 'Rose',
                'last_name' => 'Davis',
                'phone' => '09123456793',
                'section_name' => '4E-G2',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100105',
                'first_name' => 'David',
                'middle_name' => 'James',
                'last_name' => 'Wilson',
                'phone' => '09123456794',
                'section_name' => '4E-G1',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],

            // Students without submitted assessments (3 total)
            [
                'student_number' => '2022100111',
                'first_name' => 'Daniel',
                'middle_name' => 'Paul',
                'last_name' => 'Thomas',
                'phone' => '09123456800',
                'section_name' => '4E-G1',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100112',
                'first_name' => 'Jennifer',
                'middle_name' => 'Lynn',
                'last_name' => 'Jackson',
                'phone' => '09123456801',
                'section_name' => '4E-G1',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100113',
                'first_name' => 'Robert',
                'middle_name' => 'Scott',
                'last_name' => 'White',
                'phone' => '09123456802',
                'section_name' => '4E-G2',
                'specialization' => 'WMAD',
                'is_submit' => false,
            ],
            [
                'student_number' => '2022100116',
                'first_name' => 'Alex',
                'middle_name' => 'Jordan',
                'last_name' => 'Thompson',
                'phone' => '09123456805',
                'section_name' => '4E-G2',
                'specialization' => 'WMAD',
                'is_submit' => false,
            ],
            [
                'student_number' => '2022100117',
                'first_name' => 'Nicole',
                'middle_name' => 'Kay',
                'last_name' => 'Garcia',
                'phone' => '09123456806',
                'section_name' => '4E-G2',
                'specialization' => 'WMAD',
                'is_submit' => false,
            ],
        ];

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($students as $studentData) {
            // Check if student already exists
            $existingStudent = Student::where('student_number', $studentData['student_number'])->first();
            if ($existingStudent) {
                $this->command->info("Student {$studentData['student_number']} already exists. Skipping.");
                $skippedCount++;
                continue;
            }

            // Get section ID
            $section = $availableSections->where('section_name', $studentData['section_name'])->first();
            if (!$section) {
                $this->command->warn("Section {$studentData['section_name']} not found. Skipping student {$studentData['student_number']}.");
                continue;
            }

            // Create user account
            $username = $studentData['username'] ?? $studentData['student_number'];
            $password = $studentData['password'] ?? 'password';

            $user = User::create([
                'username' => $username,
                'email' => strtolower(str_replace(' ', '.', $studentData['first_name'] . ' ' . $studentData['last_name'])) . '@example.com',
                'password' => Hash::make($password),
                'status' => 'verified',
            ]);

            // Assign student role
            $user->assignRole('student');

            // Create student record
            $student = Student::create([
                'user_id' => $user->id,
                'student_number' => $studentData['student_number'],
                'first_name' => $studentData['first_name'],
                'middle_name' => $studentData['middle_name'],
                'last_name' => $studentData['last_name'],
                'phone' => $studentData['phone'],
                'section_id' => $section->section_id,
                'specialization' => $studentData['specialization'],
                'is_submit' => $studentData['is_submit'],
                'is_placed' => false,
                'is_active' => true,
                'internship_season_id' => $season->id,
            ]);

            $createdCount++;
        }

        $this->command->info("Student seeder completed!");
        $this->command->info("Created: {$createdCount} students");
        $this->command->info("Skipped: {$skippedCount} students (already existed)");

        // Display section distribution
        $this->command->info("\nSection Distribution:");
        foreach ($availableSections as $section) {
            $studentCount = Student::where('section_id', $section->section_id)->count();
            $this->command->info("Section {$section->section_name}: {$studentCount} students");
        }

        $this->call(AcademeAccountSeeder::class);

        // Generate student assessment scores and matches
        $this->generateStudentScoresAndMatches();

        // Create deadlines for the season
        $this->createDeadlinesForSeason();
    }

    /**
     * Generate realistic student assessment scores and compatibility matches
     */
    private function generateStudentScoresAndMatches(): void
    {
        $this->command->info("Generating student assessment scores and matches...");

        // Get all subcategories
        $subcategories = \App\Models\SubCategory::with('category')->get();

        // Get all internships with question importance ratings
        $internships = \App\Models\Internship::with(['questionImportanceRatings.question.subcategory', 'hte'])->get();

        // Get students who have submitted assessments
        $submittedStudents = \App\Models\Student::where('is_submit', true)->get();

        if ($submittedStudents->isEmpty()) {
            $this->command->warn("No students with submitted assessments found. Skipping score generation.");
            return;
        }

        // Define student skill profiles for realistic variation
        $skillProfiles = [
            'frontend_focused' => [
                'HTML/CSS' => [4.5, 5.0], // High proficiency
                'JavaScript' => [4.0, 5.0], // High proficiency
                'PHP' => [2.0, 3.5], // Moderate
                'Python' => [2.0, 3.0], // Low-moderate
                'Java' => [2.0, 3.0], // Low-moderate
                'C++' => [1.5, 2.5], // Low
                'SQL' => [2.5, 3.5], // Moderate
                'Web Development' => [4.0, 5.0], // High
                'Database Management' => [2.5, 3.5], // Moderate
                'System and Software Development' => [2.0, 3.5], // Low-moderate
                'Communication Skills' => [3.5, 4.5], // Good
                'Problem-Solving and Analytical Skills' => [3.0, 4.0], // Good
                'Time Management' => [3.0, 4.0], // Good
                'Adaptability and Learning' => [3.5, 4.5], // Good
                'Ethical Decision-Making' => [3.0, 4.0], // Good
                'Professionalism' => [3.5, 4.5], // Good
            ],
            'backend_focused' => [
                'HTML/CSS' => [2.5, 3.5], // Moderate
                'JavaScript' => [3.0, 4.0], // Moderate-good
                'PHP' => [4.0, 5.0], // High proficiency
                'Python' => [3.5, 4.5], // Good-high
                'Java' => [3.5, 4.5], // Good-high
                'C++' => [3.0, 4.0], // Moderate-good
                'SQL' => [4.0, 5.0], // High proficiency
                'Web Development' => [3.0, 4.0], // Moderate-good
                'Database Management' => [4.0, 5.0], // High
                'System and Software Development' => [4.0, 5.0], // High
                'Communication Skills' => [3.0, 4.0], // Good
                'Problem-Solving and Analytical Skills' => [4.0, 5.0], // High
                'Time Management' => [3.5, 4.5], // Good
                'Adaptability and Learning' => [3.5, 4.5], // Good
                'Ethical Decision-Making' => [3.5, 4.5], // Good
                'Professionalism' => [3.5, 4.5], // Good
            ],
            'fullstack_balanced' => [
                'HTML/CSS' => [3.5, 4.5], // Good-high
                'JavaScript' => [3.5, 4.5], // Good-high
                'PHP' => [3.5, 4.5], // Good-high
                'Python' => [3.0, 4.0], // Moderate-good
                'Java' => [3.0, 4.0], // Moderate-good
                'C++' => [2.5, 3.5], // Moderate
                'SQL' => [3.5, 4.5], // Good-high
                'Web Development' => [4.0, 5.0], // High
                'Database Management' => [3.5, 4.5], // Good-high
                'System and Software Development' => [3.5, 4.5], // Good-high
                'Communication Skills' => [3.5, 4.5], // Good
                'Problem-Solving and Analytical Skills' => [3.5, 4.5], // Good
                'Time Management' => [3.5, 4.5], // Good
                'Adaptability and Learning' => [4.0, 5.0], // High
                'Ethical Decision-Making' => [3.5, 4.5], // Good
                'Professionalism' => [3.5, 4.5], // Good
            ],
            'data_focused' => [
                'HTML/CSS' => [2.0, 3.0], // Low-moderate
                'JavaScript' => [2.0, 3.0], // Low-moderate
                'PHP' => [2.0, 3.0], // Low-moderate
                'Python' => [4.0, 5.0], // High proficiency
                'Java' => [3.0, 4.0], // Moderate-good
                'C++' => [2.5, 3.5], // Moderate
                'SQL' => [4.5, 5.0], // High proficiency
                'Web Development' => [2.0, 3.0], // Low-moderate
                'Database Management' => [4.5, 5.0], // High
                'System and Software Development' => [3.0, 4.0], // Moderate-good
                'Communication Skills' => [3.0, 4.0], // Good
                'Problem-Solving and Analytical Skills' => [4.5, 5.0], // High
                'Time Management' => [3.5, 4.5], // Good
                'Adaptability and Learning' => [3.5, 4.5], // Good
                'Ethical Decision-Making' => [3.5, 4.5], // Good
                'Professionalism' => [3.5, 4.5], // Good
            ],
            'mixed_skills' => [
                'HTML/CSS' => [2.5, 4.0], // Moderate-good
                'JavaScript' => [2.5, 4.0], // Moderate-good
                'PHP' => [2.5, 4.0], // Moderate-good
                'Python' => [2.5, 4.0], // Moderate-good
                'Java' => [2.5, 4.0], // Moderate-good
                'C++' => [2.0, 3.5], // Low-moderate
                'SQL' => [2.5, 4.0], // Moderate-good
                'Web Development' => [2.5, 4.0], // Moderate-good
                'Database Management' => [2.5, 4.0], // Moderate-good
                'System and Software Development' => [2.5, 4.0], // Moderate-good
                'Communication Skills' => [3.0, 4.5], // Good
                'Problem-Solving and Analytical Skills' => [3.0, 4.5], // Good
                'Time Management' => [3.0, 4.5], // Good
                'Adaptability and Learning' => [3.5, 5.0], // Good-high
                'Ethical Decision-Making' => [3.0, 4.5], // Good
                'Professionalism' => [3.0, 4.5], // Good
            ],
            'ui_ux_specialist' => [
                'HTML/CSS' => [4.5, 5.0], // High proficiency
                'JavaScript' => [3.5, 4.5], // Good-high
                'PHP' => [2.0, 3.0], // Low-moderate
                'Python' => [2.0, 3.0], // Low-moderate
                'Java' => [2.0, 3.0], // Low-moderate
                'C++' => [1.5, 2.5], // Low
                'SQL' => [2.0, 3.0], // Low-moderate
                'Web Development' => [4.0, 5.0], // High
                'Database Management' => [2.0, 3.0], // Low-moderate
                'System and Software Development' => [2.5, 3.5], // Moderate
                'Communication Skills' => [4.0, 5.0], // High
                'Problem-Solving and Analytical Skills' => [3.5, 4.5], // Good-high
                'Time Management' => [3.5, 4.5], // Good-high
                'Adaptability and Learning' => [4.0, 5.0], // High
                'Ethical Decision-Making' => [3.5, 4.5], // Good-high
                'Professionalism' => [4.0, 5.0], // High
            ],
            'python_data_expert' => [
                'HTML/CSS' => [2.0, 3.0], // Low-moderate
                'JavaScript' => [2.0, 3.0], // Low-moderate
                'PHP' => [2.0, 3.0], // Low-moderate
                'Python' => [4.5, 5.0], // High proficiency
                'Java' => [3.0, 4.0], // Moderate-good
                'C++' => [2.5, 3.5], // Moderate
                'SQL' => [4.5, 5.0], // High proficiency
                'Web Development' => [2.0, 3.0], // Low-moderate
                'Database Management' => [4.5, 5.0], // High
                'System and Software Development' => [3.5, 4.5], // Good-high
                'Communication Skills' => [3.0, 4.0], // Good
                'Problem-Solving and Analytical Skills' => [4.5, 5.0], // High
                'Time Management' => [3.5, 4.5], // Good-high
                'Adaptability and Learning' => [4.0, 5.0], // High
                'Ethical Decision-Making' => [3.5, 4.5], // Good-high
                'Professionalism' => [3.5, 4.5], // Good-high
            ],
            'java_enterprise_dev' => [
                'HTML/CSS' => [2.5, 3.5], // Moderate
                'JavaScript' => [2.5, 3.5], // Moderate
                'PHP' => [2.0, 3.0], // Low-moderate
                'Python' => [2.5, 3.5], // Moderate
                'Java' => [4.5, 5.0], // High proficiency
                'C++' => [3.0, 4.0], // Moderate-good
                'SQL' => [3.5, 4.5], // Good-high
                'Web Development' => [2.5, 3.5], // Moderate
                'Database Management' => [3.5, 4.5], // Good-high
                'System and Software Development' => [4.0, 5.0], // High
                'Communication Skills' => [3.0, 4.0], // Good
                'Problem-Solving and Analytical Skills' => [4.0, 5.0], // High
                'Time Management' => [3.5, 4.5], // Good-high
                'Adaptability and Learning' => [3.5, 4.5], // Good-high
                'Ethical Decision-Making' => [3.5, 4.5], // Good-high
                'Professionalism' => [3.5, 4.5], // Good-high
            ],
        ];

        $profileNames = array_keys($skillProfiles);
        $scoreCount = 0;
        $matchCount = 0;

        foreach ($submittedStudents as $index => $student) {
            // Assign a skill profile (cycle through profiles)
            $profileName = $profileNames[$index % count($profileNames)];
            $profile = $skillProfiles[$profileName];

            $this->command->info("Generating scores for {$student->first_name} {$student->last_name} ({$profileName})");

            // Generate scores for each subcategory
            foreach ($subcategories as $subcategory) {
                $subcategoryName = $subcategory->subcategory_name;

                // Get score range for this subcategory from the profile
                if (isset($profile[$subcategoryName])) {
                    $range = $profile[$subcategoryName];
                    $score = $this->generateRandomScore($range[0], $range[1]);
                } else {
                    // Default range for any missing subcategories
                    $score = $this->generateRandomScore(2.0, 4.0);
                }

                // Create or update student score
                \App\Models\StudentScore::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'sub_category_id' => $subcategory->id,
                    ],
                    [
                        'score' => $score,
                    ]
                );
                $scoreCount++;
            }

            // Generate compatibility scores for all internships
            $this->generateCompatibilityScores($student, $internships);
            $matchCount += $internships->count();
        }

        $this->command->info("Generated {$scoreCount} student scores and {$matchCount} compatibility matches");
    }

    /**
     * Generate compatibility scores for a student with all internships
     */
    private function generateCompatibilityScores(\App\Models\Student $student, $internships): void
    {
        // Get student's scores
        $studentScores = $student->scores()->with('subcategory')->get()->keyBy('sub_category_id');

        $compatibilityScores = collect();

        foreach ($internships as $internship) {
            $score = $this->calculateInternshipCompatibility($studentScores, $internship);

            $compatibilityScores->push([
                'internship' => $internship,
                'compatibility_score' => $score,
            ]);
        }

        // Sort by compatibility score (highest first) and assign ranks
        $rankedScores = $compatibilityScores
            ->sortByDesc('compatibility_score')
            ->values()
            ->map(function ($item, $index) {
                $item['rank'] = $index + 1;
                return $item;
            });

        // Store all scores in the student_matches table
        foreach ($rankedScores as $rankedScore) {
            \App\Models\StudentMatch::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'internship_id' => $rankedScore['internship']->id,
                ],
                [
                    'rank' => $rankedScore['rank'],
                    'compatibility_score' => $rankedScore['compatibility_score'],
                    'endorsement_status' => 'pending',
                    'placement_status' => 'pending',
                ]
            );
        }
    }

    /**
     * Calculate compatibility score between student and internship
     * Matches the logic in MatchingService::calculateInternshipCompatibility
     */
    private function calculateInternshipCompatibility($studentScores, $internship): float
    {
        $totalScore = 0;
        $totalWeight = 0;

        // Get question importance ratings for this internship
        $ratings = $internship->questionImportanceRatings;
        
        // Group ratings by subcategory
        $subcategoryRatings = [];
        $subcategoryQuestionCounts = [];
        
        foreach ($ratings as $rating) {
            if (!$rating->question || !$rating->question->subcategory) {
                continue;
            }
            
            $subcategoryId = $rating->question->subcategory->id;
            
            if (!isset($subcategoryRatings[$subcategoryId])) {
                $subcategoryRatings[$subcategoryId] = [];
                // Count total questions in this subcategory (including unrated ones)
                $subcategoryQuestionCounts[$subcategoryId] = $rating->question->subcategory->questions()
                    ->where('is_active', true)
                    ->count();
            }
            
            // Only include valid ratings (1-5)
            if ($rating->rating >= 1 && $rating->rating <= 5) {
                $subcategoryRatings[$subcategoryId][] = $rating->rating;
            }
        }

        // Calculate subcategory percentages and use them as weights
        foreach ($subcategoryRatings as $subcategoryId => $questionRatings) {
            $questionCount = $subcategoryQuestionCounts[$subcategoryId];
            
            // Calculate subcategory percentage (this becomes the "weight")
            $subcategoryPercentage = $this->calculateSubcategoryPercentage($questionRatings, $questionCount);
            
            // Skip if no valid ratings or percentage is 0
            if (empty($questionRatings) || $subcategoryPercentage == 0) {
                continue;
            }
            
            // Get student's score for this subcategory
            $studentScore = $studentScores->get($subcategoryId);
            
            if ($studentScore) {
                // Convert student score (1-5 scale) to percentage (0-100)
                $scorePercentage = ($studentScore->score / 5) * 100;
                
                // Apply subcategory percentage as weight to the score
                $weightedScore = $scorePercentage * ($subcategoryPercentage / 100);
                
                $totalScore += $weightedScore;
                $totalWeight += $subcategoryPercentage;
            }
        }

        // Calculate final compatibility score
        if ($totalWeight > 0) {
            return round(($totalScore / $totalWeight) * 100, 2);
        }

        return 0;
    }

    /**
     * Calculate subcategory percentage from question ratings
     * Formula: (sum of question ratings) / (number of questions × 5) × 100
     */
    private function calculateSubcategoryPercentage(array $questionRatings, int $questionCount): float
    {
        if ($questionCount === 0) {
            return 0;
        }
        
        $sumOfRatings = array_sum($questionRatings);
        $maxPossibleScore = $questionCount * 5;
        
        return round(($sumOfRatings / $maxPossibleScore) * 100, 2);
    }

    /**
     * Generate a random score within a range
     */
    private function generateRandomScore(float $min, float $max): float
    {
        return round($min + (mt_rand() / mt_getrandmax()) * ($max - $min), 2);
    }

    /**
     * Create deadlines for the internship season
     */
    private function createDeadlinesForSeason(): void
    {
        $this->command->info("Creating deadlines for the internship season...");

        // Get the season (either existing or newly created)
        $season = \App\Models\InternshipSeason::first();

        if (!$season) {
            $this->command->warn("No internship season found. Skipping deadline creation.");
            return;
        }

        $this->command->info("Creating deadlines for season: {$season->name}");

        // Define deadline categories in chronological order
        $deadlineCategories = [
            'hte_assessment_form' => [
                'title' => 'HTE Assessment Form Submission Deadline',
                'duration_days' => 7,
                'description' => 'Deadline for HTE companies to submit their assessment forms and criteria'
            ],
            'student_verification' => [
                'title' => 'Student Verification Deadline',
                'duration_days' => 5,
                'description' => 'Deadline for students to verify their accounts and complete registration'
            ],
            'student_assessment_form' => [
                'title' => 'Student Assessment Form Submission Deadline',
                'duration_days' => 10,
                'description' => 'Deadline for students to submit their self-assessment forms'
            ],
            'internship_placement' => [
                'title' => 'Internship Placement Deadline',
                'duration_days' => 14,
                'description' => 'Deadline for SIP endorsement and HTE placement decisions'
            ],
            'archive_students' => [
                'title' => 'Archive Students Deadline',
                'duration_days' => 3,
                'description' => 'Deadline to archive completed students and finalize the season'
            ],
        ];

        $now = \Carbon\Carbon::now();
        $createdCount = 0;
        $skippedCount = 0;

        foreach ($deadlineCategories as $category => $config) {
            // Check if deadline already exists for this category in the season
            $existingDeadline = \App\Models\Deadline::where('category', $category)
                ->where('internship_season_id', $season->id)
                ->first();

            if ($existingDeadline) {
                $this->command->warn("Deadline already exists for category: {$category} in season: {$season->name}");
                $skippedCount++;
                continue;
            }

            // Calculate start and end dates with realistic spacing
            $startDate = $now->copy()->addDays($createdCount * 2); // 2 days between each deadline start
            $endDate = $startDate->copy()->addDays($config['duration_days']);

            // Create the deadline
            \App\Models\Deadline::create([
                'title' => $config['title'],
                'category' => $category,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'internship_season_id' => $season->id,
            ]);

            $this->command->info("Created deadline: {$config['title']}");
            $this->command->info("  - Category: {$category}");
            $this->command->info("  - Start: {$startDate->format('M d, Y H:i')}");
            $this->command->info("  - End: {$endDate->format('M d, Y H:i')}");
            $this->command->info("  - Duration: {$config['duration_days']} days");
            $this->command->info("  - Description: {$config['description']}");
            $this->command->info("");

            $createdCount++;
        }

        $this->command->info("Deadline creation completed!");
        $this->command->info("Created: {$createdCount} deadlines");
        $this->command->info("Skipped: {$skippedCount} deadlines (already existed)");

        // Show deadline summary
        $this->command->info("\nDeadline Summary for Season '{$season->name}':");
        $deadlines = \App\Models\Deadline::where('internship_season_id', $season->id)
            ->orderBy('start_date', 'asc')
            ->get();

        foreach ($deadlines as $deadline) {
            $status = $deadline->status;
            $statusColor = $status === 'active' ? 'green' : ($status === 'expired' ? 'red' : 'yellow');
            $this->command->info("  - {$deadline->title} ({$status})");
            $this->command->info("    Start: {$deadline->start_date->format('M d, Y H:i')}");
            $this->command->info("    End: {$deadline->end_date->format('M d, Y H:i')}");
        }

        // Show season status
        if ($season->status === 'inactive') {
            $this->command->info("\nNote: Season '{$season->name}' is currently inactive.");
            $this->command->info("You can activate it when ready to begin the internship process.");
        } else {
            $this->command->info("\nSeason '{$season->name}' is currently active.");
        }
    }

    private function getOrCreateSeason(): InternshipSeason
    {
        // First, try to find an existing season
        $season = InternshipSeason::first();

        if ($season) {
            $this->command->info("Using existing season: {$season->name}");
            return $season;
        }

        // If no season exists, create a default one
        $this->command->warn('No internship season found. Creating a default season for students...');

        $season = InternshipSeason::create([
            'name' => 'AY 2024-2025 First Semester (Student Seeder)',
            'start_date' => now()->subMonths(1),
            'end_date' => now()->addMonths(2),
            'status' => 'active', // Start as inactive
        ]);

        $this->command->info("Created default season: {$season->name}");
        return $season;
    }

}
