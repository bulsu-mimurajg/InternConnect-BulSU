<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
          
            // Third Year Sections
            '3A-G1', '3A-G2', '3A-G3', '3A-G4',
            '3B-G1', '3B-G2', '3B-G3', '3B-G4',
            '3C-G1', '3C-G2', '3C-G3', '3C-G4',
            
        
        ];

        foreach ($sections as $sectionName) {
            Section::create([
                'section_name' => $sectionName,
                'status' => 'active'
            ]);
        }
    }
}
