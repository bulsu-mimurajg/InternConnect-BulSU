<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AcademeAccount;
use App\Models\Section;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcademeAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create sections
        $sections = Section::all();
        
        if ($sections->isEmpty()) {
            $sections = Section::create([
                'section_name' => 'BSIT-1A',
                'status' => 'active'
            ]);
            $sections = collect([$sections]);
        }

        // Get users with different roles
        $students = User::whereHas('roles', function ($query) {
            $query->where('name', 'student');
        })->get();

        $advisers = User::whereHas('roles', function ($query) {
            $query->where('name', 'adviser');
        })->get();

        // Assign students to sections
        foreach ($students as $index => $student) {
            $section = $sections->get($index % $sections->count());
            
            AcademeAccount::updateOrCreate(
                [
                    'user_id' => $student->id,
                    'section_id' => $section->section_id,
                ],
                [
                    'user_id' => $student->id,
                    'section_id' => $section->section_id,
                ]
            );
        }

        // Assign advisers to sections
        foreach ($advisers as $index => $adviser) {
            $section = $sections->get($index % $sections->count());
            
            AcademeAccount::updateOrCreate(
                [
                    'user_id' => $adviser->id,
                    'section_id' => $section->section_id,
                ],
                [
                    'user_id' => $adviser->id,
                    'section_id' => $section->section_id,
                ]
            );
        }
    }
}
