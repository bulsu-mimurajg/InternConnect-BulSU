<?php

namespace Database\Seeders;

use App\Models\HTE;
use App\Models\Internship;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InternshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all HTE records
        $htes = HTE::all();

        // Create specific internship opportunities for each HTE (reduced to minimum)
        $internshipData = [
            // TechCorp Solutions internships
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
                'slot_count' => 2,
                'is_active' => true,
            ],

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

        // Note: Removed factory-generated internships as they are not used in any functionality
    }
}
