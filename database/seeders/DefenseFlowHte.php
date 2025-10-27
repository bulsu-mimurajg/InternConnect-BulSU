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

class DefenseFlowHte extends Seeder
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
            [
                'username' => 'hte',
                'email' => 'hte@example.com',
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
            [
                'user_id' => User::where('username', 'hte')->first()->id,
                'company_name' => 'HTE Solutions',
                'company_address' => '123 Innovation Drive, HTE City, HTE 12345',
                'company_email' => 'hr@hte.com',
                'cperson_fname' => 'HTE',
                'cperson_lname' => 'HTE',
                'cperson_position' => 'HTE Manager',
                'cperson_contactnum' => '+1-555-0101',
                'is_active' => true,
                'is_submit' => false,
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
            [
                'hte_id' => $htes->where('company_name', 'TechCorp Solutions')->first()->id,
                'position_title' => 'Software Development Intern',
                'department' => 'Information Technology',
                'placement_description' => 'Join our development team and work on real-world projects using modern technologies like React, Node.js, and Python. You will participate in code reviews, attend team meetings, and contribute to our product development process.',
                'slot_count' => 3,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->where('company_name', 'TechCorp Solutions')->first()->id,
                'position_title' => 'Data Science Intern',
                'department' => 'Information Technology',
                'placement_description' => 'Work with our data science team to analyze large datasets, build predictive models, and create data visualizations. Experience with Python, SQL, and machine learning frameworks is preferred.',
                'slot_count' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($internshipData as $data) {
            Internship::create($data);
        }

        // Internship Criteria
        $categories = Category::with('subCategories')->get();

        $internships = Internship::all();

        $internshipCriteria = [
            'Full-Stack Development Intern' => [
                'Language Proficiency' => [
                    'JavaScript' => 30,
                    'PHP' => 25,
                    'HTML/CSS' => 20,
                    'SQL' => 15,
                    'Python' => 5,
                    'Java' => 3,
                    'C++' => 2,
                ],
                'Technical Skill' => [
                    'Web Development' => 60,
                    'Database Management' => 25,
                    'System and Software Development' => 15,
                ],
                'Soft Skill' => [
                    'Communication Skills' => 25,
                    'Problem-Solving and Analytical Skills' => 20,
                    'Time Management' => 15,
                    'Adaptability and Learning' => 15,
                    'Professionalism' => 15,
                    'Ethical Decision-Making' => 10,
                ],
            ],
            'UI/UX Design Intern' => [
                'Language Proficiency' => [
                    'HTML/CSS' => 10,
                    'JavaScript' => 10,
                    'Python' => 15,
                    'Java' => 20,
                    'C++' => 20,
                    'PHP' => 15,
                    'SQL' => 10,
                ],
                'Technical Skill' => [
                    'Web Development' => 40,
                    'Database Management' => 30,
                    'System and Software Development' => 30,
                ],
                'Soft Skill' => [
                    'Communication Skills' => 30,
                    'Problem-Solving and Analytical Skills' => 25,
                    'Time Management' => 15,
                    'Adaptability and Learning' => 15,
                    'Professionalism' => 10,
                    'Ethical Decision-Making' => 5,
                ],
            ],
            'Software Development Intern' => [
                'Language Proficiency' => [
                    'JavaScript' => 25,
                    'Python' => 25,
                    'Java' => 20,
                    'C++' => 15,
                    'HTML/CSS' => 10,
                    'PHP' => 3,
                    'SQL' => 2,
                ],
                'Technical Skill' => [
                    'System and Software Development' => 70,
                    'Web Development' => 20,
                    'Database Management' => 10,
                ],
                'Soft Skill' => [
                    'Problem-Solving and Analytical Skills' => 30,
                    'Communication Skills' => 20,
                    'Time Management' => 20,
                    'Adaptability and Learning' => 15,
                    'Professionalism' => 10,
                    'Ethical Decision-Making' => 5,
                ],
            ],
            'Data Science Intern' => [
                'Language Proficiency' => [
                    'Python' => 30,
                    'SQL' => 20,
                    'JavaScript' => 5,
                    'Java' => 20,
                    'C++' => 5,
                    'HTML/CSS' => 10,
                    'PHP' => 10,
                ],
                'Technical Skill' => [
                    'Database Management' => 60,
                    'System and Software Development' => 30,
                    'Web Development' => 10,
                ],
                'Soft Skill' => [
                    'Problem-Solving and Analytical Skills' => 40,
                    'Communication Skills' => 25,
                    'Time Management' => 15,
                    'Adaptability and Learning' => 10,
                    'Professionalism' => 5,
                    'Ethical Decision-Making' => 5,
                ],
            ],
        ];

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
    }
}
