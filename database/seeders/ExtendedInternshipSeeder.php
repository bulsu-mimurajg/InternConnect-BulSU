<?php

namespace Database\Seeders;

use App\Models\HTE;
use App\Models\Internship;
use Illuminate\Database\Seeder;

class ExtendedInternshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing HTEs
        $htes = HTE::all();
        
        if ($htes->isEmpty()) {
            $this->command->warn('No HTEs found. Please run HTESeeder first.');
            return;
        }

        // Define additional internship positions for better data diversity
        $additionalInternships = [
            [
                'hte_id' => $htes->random()->id,
                'position_title' => 'Cybersecurity Intern',
                'department' => 'Information Security',
                'placement_description' => 'Assist in cybersecurity operations, threat analysis, and security assessments. Learn about network security, vulnerability management, and incident response.',
                'slot_count' => 3,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->random()->id,
                'position_title' => 'UX/UI Design Intern',
                'department' => 'Product Design',
                'placement_description' => 'Work on user experience and interface design projects. Learn design principles, prototyping tools, and user research methodologies.',
                'slot_count' => 2,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->random()->id,
                'position_title' => 'AI/ML Intern',
                'department' => 'Artificial Intelligence',
                'placement_description' => 'Contribute to machine learning projects, data preprocessing, and model development. Gain experience with AI frameworks and algorithms.',
                'slot_count' => 2,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->random()->id,
                'position_title' => 'DevOps Intern',
                'department' => 'Infrastructure',
                'placement_description' => 'Learn about continuous integration/deployment, cloud infrastructure, and automation tools. Work with Docker, Kubernetes, and CI/CD pipelines.',
                'slot_count' => 2,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->random()->id,
                'position_title' => 'Mobile App Development Intern',
                'department' => 'Mobile Development',
                'placement_description' => 'Develop mobile applications for iOS and Android platforms. Learn mobile development frameworks and best practices.',
                'slot_count' => 3,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->random()->id,
                'position_title' => 'Quality Assurance Intern',
                'department' => 'Quality Assurance',
                'placement_description' => 'Learn software testing methodologies, automated testing tools, and quality assurance processes. Contribute to ensuring software quality.',
                'slot_count' => 2,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->random()->id,
                'position_title' => 'Business Intelligence Intern',
                'department' => 'Business Intelligence',
                'placement_description' => 'Work with data warehousing, reporting tools, and business analytics. Learn to create dashboards and data-driven insights.',
                'slot_count' => 2,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->random()->id,
                'position_title' => 'Cloud Computing Intern',
                'department' => 'Cloud Infrastructure',
                'placement_description' => 'Learn about cloud platforms (AWS, Azure, GCP), infrastructure as code, and cloud-native development practices.',
                'slot_count' => 2,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->random()->id,
                'position_title' => 'Game Development Intern',
                'department' => 'Game Development',
                'placement_description' => 'Contribute to game development projects using Unity or Unreal Engine. Learn game design principles and development workflows.',
                'slot_count' => 2,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->random()->id,
                'position_title' => 'Blockchain Intern',
                'department' => 'Blockchain Technology',
                'placement_description' => 'Learn about blockchain technology, smart contracts, and decentralized applications. Work with blockchain platforms and tools.',
                'slot_count' => 1,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->random()->id,
                'position_title' => 'IoT Development Intern',
                'department' => 'Internet of Things',
                'placement_description' => 'Work on IoT projects involving sensors, embedded systems, and connected devices. Learn about IoT protocols and platforms.',
                'slot_count' => 2,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->random()->id,
                'position_title' => 'Data Engineering Intern',
                'department' => 'Data Engineering',
                'placement_description' => 'Learn about data pipelines, ETL processes, and big data technologies. Work with data processing frameworks and tools.',
                'slot_count' => 2,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->random()->id,
                'position_title' => 'Frontend Development Intern',
                'department' => 'Frontend Development',
                'placement_description' => 'Focus on modern frontend technologies like React, Vue.js, and Angular. Learn responsive design and user interface development.',
                'slot_count' => 3,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->random()->id,
                'position_title' => 'Backend Development Intern',
                'department' => 'Backend Development',
                'placement_description' => 'Work on server-side development, APIs, and database design. Learn about microservices architecture and backend frameworks.',
                'slot_count' => 3,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->random()->id,
                'position_title' => 'Full Stack Development Intern',
                'department' => 'Full Stack Development',
                'placement_description' => 'Gain experience in both frontend and backend development. Work on complete web applications and learn full-stack technologies.',
                'slot_count' => 2,
                'is_active' => true,
            ]
        ];

        // Create additional internships
        foreach ($additionalInternships as $internshipData) {
            Internship::firstOrCreate(
                [
                    'position_title' => $internshipData['position_title'],
                    'hte_id' => $internshipData['hte_id'],
                ],
                $internshipData
            );
        }

        $this->command->info('Extended internship positions seeded successfully!');
    }
}
