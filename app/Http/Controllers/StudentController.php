<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Section;
use App\Models\User;
use App\Services\AdviserNotificationService;
use App\Services\StudentPlacementNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    /**
     * Show the student signup form
     */
    public function showSignupForm()
    {
        $sections = Section::where('status', 'active')->get();
        return view('students.signup', compact('sections'));
    }

    /**
     * Handle student signup
     */
    public function signup(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'required|string|max:20',
            'section_id' => 'required|integer|exists:sections,section_id',
            'specialization' => 'required|string|max:10',
            'address' => 'required|string|max:255',
            'birth_date' => 'required|date',
        ]);

        // Check if section exists
        $section = Section::find($request->section_id);
        if (!$section) {
            return back()->withErrors(['section_id' => 'Invalid section selected'])->withInput();
        }

        // Create user account
        $user = User::create([
            'username' => strtolower($request->first_name) . '.' . strtolower($request->last_name),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
            'status' => 'verified',
        ]);

        // Assign student role
        $user->assignRole('student');

        // Create the student record
        $student = Student::create([
            'user_id' => $user->id,
            'student_number' => $request->student_number,
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'section_id' => $request->section_id,
            'specialization' => $validated['specialization'],
            'address' => $validated['address'],
            'birth_date' => $validated['birth_date'],
            'is_submit' => false,
            'is_placed' => false,
            'is_active' => true,
        ]);

        // Load the section relationship for the notification
        $student->load('section');

        // Notify advisers about the new student registration
        $adviserNotificationService = new AdviserNotificationService();
        $adviserNotificationService->notifyAdviserForNewStudentRegistration($student);

        return redirect()->route('student.dashboard')->with('success', 'Student account created successfully!');
    }

    /**
     * Get students by section
     */
    public function getStudentsBySection($sectionId)
    {
        $section = Section::find($sectionId);
        
        if (!$section) {
            return response()->json(['error' => 'Section not found'], 404);
        }

        $students = $section->students()->with('user')->get();
        
        return response()->json([
            'section' => $section->section_name,
            'students' => $students
        ]);
    }

    /**
     * Get all sections with student counts
     */
    public function getSectionsWithStudentCounts()
    {
        $sections = Section::withCount('students')->get();
        
        return response()->json($sections);
    }

    /**
     * Update student section
     */
    public function updateSection(Request $request, $studentId)
    {
        $validator = Validator::make($request->all(), [
            'section_id' => 'required|integer|exists:sections,section_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $student = Student::findOrFail($studentId);
        // Validate the request
        $validated = $request->validate([
            'section_id' => 'required|integer|exists:sections,section_id',
        ]);

        // Update the student's section
        $student->update(['section_id' => $validated['section_id']]);

        return response()->json([
            'message' => 'Student section updated successfully',
            'student' => $student->load('section')
        ]);
    }

    /**
     * Example method showing how to trigger notifications when a student is placed
     * This would typically be called from a placement service or controller
     */
    public function notifyStudentPlacement(Student $student, array $placementData)
    {
        $placementService = new StudentPlacementNotificationService();
        $placementService->notifyStudentForPlacement($student, $placementData);
        
        return response()->json([
            'message' => 'Placement notification sent successfully'
        ]);
    }

    /**
     * Example method showing how to trigger adviser notifications when a student registers
     * This would typically be called from the signup process
     */
    public function notifyAdviserForNewStudent(Student $student)
    {
        $adviserService = new AdviserNotificationService();
        $adviserService->notifyAdviserForStudentApproval($student);
        
        return response()->json([
            'message' => 'Adviser notification sent successfully'
        ]);
    }
}
