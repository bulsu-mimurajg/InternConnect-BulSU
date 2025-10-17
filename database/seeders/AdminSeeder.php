<?php

namespace Database\Seeders;

use App\Models\Adviser;
use App\Models\Category;
use App\Models\HTE;
use App\Models\Internship;
use App\Models\Section;
use App\Models\SubcategoryWeight;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(SectionSeeder::class);

        User::factory()->admin()->create([
            'username' => 'faye',
            'email' => 'faye@example.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        User::factory()->hte()->create([
            'username' => 'maria',
            'email' => 'maria@example.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        User::factory()->adviser()->create([
            'username' => 'emman',
            'email' => 'emman@example.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        // Get the user with username 'emman' (created in DatabaseSeeder)
        $emmanUser = User::where('username', 'emman')->first();

        // Get all available sections
        $sections = Section::where('status', 'active')->get();

        $advisers = [
            [
                'adviser_fname' => 'Emmanuel',
                'adviser_lname' => 'Santos',
                'is_active' => true,
                'section_ids' => [$sections->first()->section_id ?? 1], // Single section
                'user_id' => $emmanUser->id ?? 1,
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

        // Create unverified student following the complete registration flow
        $threeAG1Section = \App\Models\Section::where('section_name', '3A-G1')->first();
        if ($threeAG1Section) {
            // 1. Create user account (same as registration flow)
            $unverifiedStudentUser = User::create([
                'username' => '2024100123', // Student number format
                'email' => 'george.miller@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(), // Email verified
                'status' => 'unverified', // Needs adviser approval
            ]);

            // 2. Assign student role
            $unverifiedStudentUser->assignRole('student');

            // 3. Cache registration data (simulating what happens during registration)
            $registrationData = [
                'first_name' => 'George',
                'last_name' => 'Miller',
                'middle_name' => '',
                'username' => '2024100123',
                'email' => 'george.miller@example.com',
                'contact_number' => '09123456789',
                'password' => bcrypt('password'),
                'section_id' => $threeAG1Section->section_id,
                'specialization' => 'WMAD',
                'created_at' => now(),
            ];

            // Store registration data for when adviser approves (24 hours expiry)
            \Illuminate\Support\Facades\Cache::put("registration_data_{$unverifiedStudentUser->email}", $registrationData, now()->addHours(24));

            // 4. Create academe account (same way as registration)
            \App\Models\AcademeAccount::create([
                'user_id' => $unverifiedStudentUser->id,
                'section_id' => $threeAG1Section->section_id,
            ]);

            // 5. Create request record (same way as registration)
            \App\Models\Request::create([
                'stud_num' => $unverifiedStudentUser->username,
                'section_id' => $threeAG1Section->section_id,
            ]);

            // 6. Create notification for Emman (adviser) about new student needing approval
            $emmanUser = User::where('username', 'emman')->first();
            if ($emmanUser) {
                \App\Models\Notification::create([
                    'user_id' => $emmanUser->id,
                    'type' => 'student_approval_request',
                    'title' => 'New Student Registration',
                    'message' => 'George Miller has registered and is awaiting your approval.',
                    'data' => [
                        'adviser_id' => $emmanUser->id,
                        'student_username' => $unverifiedStudentUser->username,
                        'student_email' => $unverifiedStudentUser->email,
                        'student_name' => 'George Miller',
                        'section_id' => $threeAG1Section->section_id,
                        'section_name' => $threeAG1Section->section_name,
                    ],
                    'is_read' => false,
                ]);
            }
        }

        // Create HTE record for 'maria' user (created in DatabaseSeeder)
        $mariaUser = User::where('username', 'maria')->first();
        if ($mariaUser) {
            HTE::firstOrCreate(
                ['user_id' => $mariaUser->id],
                [
                    'user_id' => $mariaUser->id,
                    'company_name' => 'MariaTech Solutions',
                    'company_address' => '888 Innovation Plaza, Tech Hub District, Manila 1000',
                    'company_email' => 'maria@mariatech.com',
                    'cperson_fname' => 'Maria',
                    'cperson_lname' => 'Santos',
                    'cperson_position' => 'HR Director',
                    'cperson_contactnum' => '+63-917-123-4567',
                    'is_active' => true,
                    'is_submit' => true,
                ]
            );
        }

        // Get all HTE records
        $htes = HTE::all();

        $internshipData = [
            // MariaTech Solutions internships
            [
                'hte_id' => $htes->where('company_name', 'MariaTech Solutions')->first()->id,
                'position_title' => 'Full-Stack Development Intern',
                'department' => 'Information Technology',
                'placement_description' => 'Work on both frontend and backend development using modern frameworks like React, Laravel, and Node.js. You will participate in the complete software development lifecycle and work on real client projects.',
                'slot_count' => 4,
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
        ];

        foreach ($internshipData as $data) {
            Internship::create($data);
        }

        // Get all categories and subcategories
        $categories = Category::with('subCategories')->get();

        // Get all internships
        $internships = Internship::all();

        // Define comprehensive criteria for each internship type using only existing subcategories
        $internshipCriteria = [
            'Software Development Intern' => [
                'Language Proficiency' => [
                    'Java' => 20,
                    'Python' => 15,
                    'JavaScript' => 15,
                    'C++' => 10,
                    'SQL' => 5,
                    'HTML/CSS' => 5,
                ],
                'Technical Skill' => [
                    'System and Software Development' => 25,
                    'Web Development' => 20,
                    'Database Management' => 10,
                ],
                'Soft Skill' => [
                    'Problem-Solving and Analytical Skills' => 20,
                    'Communication Skills' => 15,
                    'Time Management' => 10,
                ]
            ],

            'Data Analytics Intern' => [
                'Language Proficiency' => [
                    'Python' => 25,
                    'SQL' => 20,
                    'JavaScript' => 5,
                    'Java' => 5,
                ],
                'Technical Skill' => [
                    'Database Management' => 30,
                    'System and Software Development' => 20,
                ],
                'Soft Skill' => [
                    'Problem-Solving and Analytical Skills' => 25,
                    'Communication Skills' => 15,
                    'Adaptability and Learning' => 10,
                ]
            ],

            'Environmental Research Intern' => [
                'Language Proficiency' => [
                    'Python' => 20,
                    'SQL' => 15,
                    'JavaScript' => 5,
                ],
                'Technical Skill' => [
                    'System and Software Development' => 25,
                ],
                'Soft Skill' => [
                    'Communication Skills' => 25,
                    'Problem-Solving and Analytical Skills' => 20,
                    'Adaptability and Learning' => 15,
                ]
            ],

            'Digital Marketing Intern' => [
                'Language Proficiency' => [
                    'HTML/CSS' => 15,
                    'JavaScript' => 15,
                    'Python' => 10,
                    'SQL' => 5,
                ],
                'Technical Skill' => [
                    'Web Development' => 25,
                    'System and Software Development' => 15,
                ],
                'Soft Skill' => [
                    'Communication Skills' => 25,
                    'Problem-Solving and Analytical Skills' => 20,
                    'Time Management' => 10,
                    'Adaptability and Learning' => 10,
                ]
            ],

            'Finance Intern' => [
                'Language Proficiency' => [
                    'SQL' => 25,
                    'Python' => 20,
                    'Java' => 5,
                ],
                'Technical Skill' => [
                    'Database Management' => 25,
                    'System and Software Development' => 15,
                ],
                'Soft Skill' => [
                    'Communication Skills' => 20,
                    'Problem-Solving and Analytical Skills' => 20,
                    'Professionalism' => 15,
                    'Time Management' => 10,
                ]
            ],

            'Cybersecurity Intern' => [
                'Language Proficiency' => [
                    'Python' => 25,
                    'JavaScript' => 15,
                    'C++' => 15,
                    'SQL' => 10,
                ],
                'Technical Skill' => [
                    'System and Software Development' => 30,
                    'Web Development' => 15,
                    'Database Management' => 10,
                ],
                'Soft Skill' => [
                    'Problem-Solving and Analytical Skills' => 25,
                    'Communication Skills' => 15,
                    'Ethical Decision-Making' => 15,
                    'Adaptability and Learning' => 10,
                ]
            ],

            'UX/UI Design Intern' => [
                'Language Proficiency' => [
                    'HTML/CSS' => 20,
                    'JavaScript' => 15,
                    'Python' => 10,
                    'SQL' => 5,
                ],
                'Technical Skill' => [
                    'Web Development' => 25,
                    'System and Software Development' => 15,
                ],
                'Soft Skill' => [
                    'Communication Skills' => 25,
                    'Problem-Solving and Analytical Skills' => 15,
                    'Time Management' => 10,
                ]
            ],

            'AI/ML Intern' => [
                'Language Proficiency' => [
                    'Python' => 30,
                    'SQL' => 15,
                    'C++' => 10,
                ],
                'Technical Skill' => [
                    'System and Software Development' => 25,
                    'Database Management' => 20,
                ],
                'Soft Skill' => [
                    'Problem-Solving and Analytical Skills' => 25,
                    'Communication Skills' => 15,
                    'Adaptability and Learning' => 15,
                ]
            ],

            'Full-Stack Development Intern' => [
                'Language Proficiency' => [
                    'JavaScript' => 25,
                    'Python' => 20,
                    'Java' => 15,
                    'HTML/CSS' => 15,
                    'SQL' => 10,
                    'C++' => 5,
                ],
                'Technical Skill' => [
                    'System and Software Development' => 30,
                    'Web Development' => 25,
                    'Database Management' => 15,
                ],
                'Soft Skill' => [
                    'Problem-Solving and Analytical Skills' => 20,
                    'Communication Skills' => 15,
                    'Time Management' => 10,
                    'Adaptability and Learning' => 10,
                ]
            ],

            'UI/UX Design Intern' => [
                'Language Proficiency' => [
                    'HTML/CSS' => 25,
                    'JavaScript' => 20,
                    'Python' => 5,
                ],
                'Technical Skill' => [
                    'Web Development' => 30,
                    'System and Software Development' => 15,
                ],
                'Soft Skill' => [
                    'Communication Skills' => 25,
                    'Problem-Solving and Analytical Skills' => 20,
                    'Time Management' => 10,
                    'Adaptability and Learning' => 10,
                ]
            ],

            'DevOps Intern' => [
                'Language Proficiency' => [
                    'Python' => 30,
                    'JavaScript' => 20,
                    'SQL' => 15,
                    'Java' => 10,
                    'C++' => 5,
                ],
                'Technical Skill' => [
                    'System and Software Development' => 35,
                    'Web Development' => 15,
                    'Database Management' => 10,
                ],
                'Soft Skill' => [
                    'Problem-Solving and Analytical Skills' => 25,
                    'Communication Skills' => 15,
                    'Adaptability and Learning' => 15,
                    'Time Management' => 10,
                ]
            ]
        ];

        // Create criteria for each internship
        foreach ($internshipCriteria as $positionTitle => $categoryCriteria) {
            $internship = $internships->where('position_title', $positionTitle)->first();

            if (!$internship) {
                continue; // Skip if internship doesn't exist
            }

            foreach ($categoryCriteria as $categoryName => $subcategoryWeights) {
                $category = $categories->where('category_name', $categoryName)->first();

                if (!$category) {
                    continue;
                }

                foreach ($subcategoryWeights as $subcategoryName => $weight) {
                    $subcategory = $category->subCategories->where('subcategory_name', $subcategoryName)->first();

                    if (!$subcategory) {
                        continue;
                    }

                    // Create or update the subcategory weight
                    SubcategoryWeight::updateOrCreate(
                        [
                            'internship_id' => $internship->id,
                            'subcategory_id' => $subcategory->id,
                        ],
                        [
                            'weight' => $weight,
                        ]
                    );
                }
            }
        }

//        $this->call(DeadlineSeeder::class);

//        // Create adviser user if it doesn't exist
//        $adviserUser = User::firstOrCreate(
//            ['email' => 'lesutemp@gmail.com'],
//            [
//                'username' => 'adv',
//                'status' => 'verified',
//                'password' => bcrypt('password'),
//                'email_verified_at' => now(),
//            ]
//        );
//
//        // Assign adviser role if not already assigned
//        if (!$adviserUser->hasRole('adviser')) {
//            $adviserUser->assignRole('adviser');
//        }
//
//        // Create adviser profile if it doesn't exist
//        $adviser = \App\Models\Adviser::firstOrCreate(
//            ['user_id' => $adviserUser->id],
//            [
//                'adviser_fname' => 'Lesu',
//                'adviser_lname' => 'Temp',
//                'is_active' => true,
//            ]
//        );
//
//        // Associate adviser with section '3A-G1' if not already associated
//        $section = \App\Models\Section::where('section_name', '3A-G1')->first();
//        if ($section && !$adviser->sections()->where('sections.section_id', $section->section_id)->exists()) {
//            $adviser->sections()->attach($section->section_id);
//        }
//

    }
}
