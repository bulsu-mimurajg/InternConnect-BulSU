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

        // Create specific internship opportunities for each HTE
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

            // DataFlow Analytics internships
            [
                'hte_id' => $htes->where('company_name', 'DataFlow Analytics')->first()->id,
                'position_title' => 'Data Analytics Intern',
                'department' => 'Research & Development',
                'placement_description' => 'Assist in collecting, cleaning, and analyzing data to provide insights for business decisions. You will work with tools like Tableau, Power BI, and SQL.',
                'slot_count' => 4,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->where('company_name', 'DataFlow Analytics')->first()->id,
                'position_title' => 'Business Intelligence Intern',
                'department' => 'Operations',
                'placement_description' => 'Help develop and maintain dashboards and reports for various business units. Experience with data visualization and business intelligence tools is a plus.',
                'slot_count' => 2,
                'is_active' => true,
            ],

            // GreenTech Industries internships
            [
                'hte_id' => $htes->where('company_name', 'GreenTech Industries')->first()->id,
                'position_title' => 'Environmental Research Intern',
                'department' => 'Research & Development',
                'placement_description' => 'Conduct research on sustainable technologies and environmental impact assessments. You will work with our sustainability team to develop eco-friendly solutions.',
                'slot_count' => 3,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->where('company_name', 'GreenTech Industries')->first()->id,
                'position_title' => 'Marketing Intern',
                'department' => 'Marketing',
                'placement_description' => 'Support our marketing team in promoting our green technology solutions. You will help create content, manage social media, and assist with marketing campaigns.',
                'slot_count' => 2,
                'is_active' => true,
            ],

            // Creative Marketing Pro internships
            [
                'hte_id' => $htes->where('company_name', 'Creative Marketing Pro')->first()->id,
                'position_title' => 'Digital Marketing Intern',
                'department' => 'Marketing',
                'placement_description' => 'Learn digital marketing strategies including SEO, SEM, social media marketing, and content creation. You will work on real client campaigns and track performance metrics.',
                'slot_count' => 5,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->where('company_name', 'Creative Marketing Pro')->first()->id,
                'position_title' => 'Graphic Design Intern',
                'department' => 'Marketing',
                'placement_description' => 'Create visual content for various marketing campaigns including social media graphics, website assets, and print materials. Proficiency in Adobe Creative Suite is preferred.',
                'slot_count' => 2,
                'is_active' => true,
            ],

            // FinanceFirst Bank internships
            [
                'hte_id' => $htes->where('company_name', 'FinanceFirst Bank')->first()->id,
                'position_title' => 'Finance Intern',
                'department' => 'Finance',
                'placement_description' => 'Assist with financial analysis, budgeting, and reporting. You will work with our finance team to analyze financial data and prepare reports for management.',
                'slot_count' => 3,
                'is_active' => true,
            ],
            [
                'hte_id' => $htes->where('company_name', 'FinanceFirst Bank')->first()->id,
                'position_title' => 'Risk Management Intern',
                'department' => 'Operations',
                'placement_description' => 'Support our risk management team in identifying, assessing, and monitoring various types of risks. You will help develop risk mitigation strategies.',
                'slot_count' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($internshipData as $data) {
            Internship::create($data);
        }

        // Create additional random internships using factory
        // This will create internships for the factory-generated HTEs as well
        Internship::factory(20)->create();
    }
}
