<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentMatch;
use App\Models\Endorsement;
use App\Models\StudentPlacement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentArchiveService
{
    /**
     * Archive students by season
     */
    public function archiveStudentsBySeason(int $seasonId): int
    {
        return DB::transaction(function () use ($seasonId) {
            $students = Student::where('internship_season_id', $seasonId)
                ->where('is_active', true)
                ->get();

            $archivedCount = 0;

            foreach ($students as $student) {
                $this->archiveStudent($student);
                $archivedCount++;
            }

            Log::info('Bulk archived students by season', [
                'season_id' => $seasonId,
                'archived_count' => $archivedCount,
            ]);

            return $archivedCount;
        });
    }

    /**
     * Archive a single student
     */
    public function archiveStudent(Student $student): bool
    {
        return DB::transaction(function () use ($student) {
            // Check if student is already archived
            if (!$student->is_active) {
                return false;
            }

            // Reset student_matches endorsement status to pending before deleting endorsements
            StudentMatch::where('student_id', $student->id)
                ->where('endorsement_status', 'endorsed')
                ->update(['endorsement_status' => 'pending']);

            // Delete any active endorsements for this student to free up slots
            Endorsement::where('student_id', $student->id)
                ->where('status', 'endorsed')
                ->delete();

            // Reset student_matches placement status to pending before deleting placements
            StudentMatch::where('student_id', $student->id)
                ->where('placement_status', 'approved')
                ->update(['placement_status' => 'pending']);

            // Delete any approved placements for this student to free up slots
            StudentPlacement::where('student_id', $student->id)
                ->where('status', 'approved')
                ->delete();

            // Archive the student
            $student->update(['is_active' => false]);

            // Reset the student's placement status since they've been removed from placements
            $student->update(['is_placed' => false]);

            Log::info('Archived student', [
                'student_id' => $student->id,
                'student_number' => $student->student_number,
                'name' => $student->first_name . ' ' . $student->last_name,
            ]);

            return true;
        });
    }

    /**
     * Check if student number can be reused
     */
    public function canReuseStudentNumber(string $studentNumber): bool
    {
        return Student::isStudentNumberAvailable($studentNumber);
    }

    /**
     * Validate student number reuse
     */
    public function validateStudentNumberReuse(string $studentNumber): array
    {
        $isAvailable = $this->canReuseStudentNumber($studentNumber);
        
        if (!$isAvailable) {
            $existingStudent = Student::where('student_number', $studentNumber)
                ->where('is_active', true)
                ->first();

            return [
                'can_reuse' => false,
                'message' => "Student number '{$studentNumber}' is already in use by an active student.",
                'existing_student' => $existingStudent,
            ];
        }

        // Check if there's an archived student with this number
        $archivedStudent = Student::where('student_number', $studentNumber)
            ->where('is_active', false)
            ->first();

        return [
            'can_reuse' => true,
            'message' => $archivedStudent 
                ? "Student number '{$studentNumber}' is available for reuse (previously archived)."
                : "Student number '{$studentNumber}' is available.",
            'archived_student' => $archivedStudent,
        ];
    }

    /**
     * Get archived students for a season
     */
    public function getArchivedStudentsBySeason(int $seasonId): \Illuminate\Database\Eloquent\Collection
    {
        return Student::where('internship_season_id', $seasonId)
            ->where('is_active', false)
            ->with(['user', 'section', 'internshipSeason'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    /**
     * Get all archived students
     */
    public function getAllArchivedStudents(): \Illuminate\Database\Eloquent\Collection
    {
        return Student::where('is_active', false)
            ->with(['user', 'section', 'internshipSeason'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    /**
     * Restore an archived student (if needed for admin purposes)
     */
    public function restoreStudent(Student $student): bool
    {
        return DB::transaction(function () use ($student) {
            if ($student->is_active) {
                return false; // Already active
            }

            // Check if student number is still available
            if (!$this->canReuseStudentNumber($student->student_number)) {
                throw new \Exception("Cannot restore student. Student number '{$student->student_number}' is now in use by another active student.");
            }

            $student->update(['is_active' => true]);

            Log::info('Restored archived student', [
                'student_id' => $student->id,
                'student_number' => $student->student_number,
                'name' => $student->first_name . ' ' . $student->last_name,
            ]);

            return true;
        });
    }

    /**
     * Get archive statistics
     */
    public function getArchiveStats(): array
    {
        $totalStudents = Student::count();
        $activeStudents = Student::where('is_active', true)->count();
        $archivedStudents = Student::where('is_active', false)->count();

        $archivedBySeason = Student::where('is_active', false)
            ->with('internshipSeason')
            ->get()
            ->groupBy('internshipSeason.name')
            ->map(function ($students) {
                return $students->count();
            });

        return [
            'total_students' => $totalStudents,
            'active_students' => $activeStudents,
            'archived_students' => $archivedStudents,
            'archived_by_season' => $archivedBySeason,
        ];
    }
}
