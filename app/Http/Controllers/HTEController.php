<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Models\HTE;
use App\Models\Internship;
use App\Models\SubcategoryWeight;
use App\Models\Category;
use App\Models\SubCategory;
use Inertia\Inertia;

class HTEController extends Controller
{
    /**
     * Check if user already has an HTE
     */
    public function checkExistingHTE()
    {
        $user = Auth::user();
        $existingHTE = $user->hte;
        
        return response()->json([
            'hasExistingHTE' => $existingHTE !== null,
            'hasSubmitted' => $existingHTE ? $existingHTE->is_submit : false,
            'hte' => $existingHTE
        ]);
    }

    /**
     * Show HTE form (always accessible, but content changes based on submission status)
     */
    public function showForm()
    {
        $user = Auth::user();
        $hte = $user->hte;
        
        return Inertia::render('hte/form', [
            'hte' => $hte,
            'hasSubmitted' => $hte ? $hte->is_submit : false
        ]);
    }

    /**
     * Handle HTE form submission
     */
    public function submit(Request $request): RedirectResponse
    {
        // Check if user already has an HTE and has submitted
        $user = Auth::user();
        if ($user->hte && $user->hte->is_submit) {
            return redirect()->back()->withErrors(['error' => 'You have already submitted an HTE form. You cannot submit multiple forms.']);
        }

        // Validate the request
        $request->validate([
            'companyName' => 'required|string|max:100',
            'contactPerson' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'numberOfInterns' => 'required|string|max:50',
            'duration' => 'required|string|max:100',
            'startDate' => 'required|string|max:50',
            'endDate' => 'required|string|max:50',
            'subcategoryWeights' => 'required|array',
        ]);

        // Debug: Log the incoming request data
        Log::info('HTE Form Submission - Request Data:', [
            'subcategoryWeights' => $request->subcategoryWeights,
            'subcategoryWeights_count' => count($request->subcategoryWeights),
            'all_request_data' => $request->all()
        ]);

        try {
            // Check if HTE record exists but hasn't been submitted
            if ($user->hte && !$user->hte->is_submit) {
                // Update existing HTE record
                $hte = $user->hte;
                $hte->update([
                    'company_name' => $request->companyName,
                    'company_address' => $request->address,
                    'company_email' => $request->email,
                    'cperson_fname' => $request->contactPerson,
                    'cperson_lname' => '',
                    'cperson_position' => $request->position,
                    'cperson_contactnum' => $request->phone,
                    'is_active' => true,
                    'is_submit' => true,
                ]);
            } else {
                // Create new HTE record
                $hte = HTE::create([
                    'user_id' => $user->id,
                    'company_name' => $request->companyName,
                    'company_address' => $request->address,
                    'company_email' => $request->email,
                    'cperson_fname' => $request->contactPerson,
                    'cperson_lname' => '',
                    'cperson_position' => $request->position,
                    'cperson_contactnum' => $request->phone,
                    'is_active' => true,
                    'is_submit' => true,
                ]);
            }

            Log::info('HTE Record Created:', ['hte_id' => $hte->id]);

            // Create Internship record
            $internship = Internship::create([
                'hte_id' => $hte->id,
                'position_title' => $request->position,
                'department' => $request->department,
                'placement_description' => 'Internship opportunity at ' . $request->companyName . ' - Duration: ' . $request->duration . ' from ' . $request->startDate . ' to ' . $request->endDate,
                'slot_count' => (int) $request->numberOfInterns,
                'is_active' => true,
            ]);

            Log::info('Internship Record Created:', ['internship_id' => $internship->id]);

            // Store subcategory weights
            $weightsCreated = 0;
            foreach ($request->subcategoryWeights as $subcategoryId => $weight) {
                try {
                    $subcategoryWeight = SubcategoryWeight::create([
                        'internship_id' => $internship->id,
                        'subcategory_id' => $subcategoryId,
                        'weight' => (int) $weight,
                    ]);
                    $weightsCreated++;
                    Log::info('Subcategory Weight Created:', [
                        'id' => $subcategoryWeight->id,
                        'internship_id' => $internship->id,
                        'subcategory_id' => $subcategoryId,
                        'weight' => $weight
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create subcategory weight:', [
                        'subcategory_id' => $subcategoryId,
                        'weight' => $weight,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            Log::info('Subcategory Weights Summary:', [
                'total_weights_processed' => count($request->subcategoryWeights),
                'weights_successfully_created' => $weightsCreated
            ]);

            return redirect()->route('hte.profile')->with('success', 'HTE form submitted successfully!');

        } catch (\Exception $e) {
            Log::error('HTE Form Submission Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'An error occurred while submitting the form. Please try again.']);
        }
    }

    /**
     * Get categories with subcategories and questions for the criteria step
     * Excludes 'Basic Information' category
     */
    public function getCategoriesForCriteria()
    {
        // Get categories with subcategories and questions, excluding 'Basic Information'
        $categories = Category::with(['subCategories.questions' => function($query) {
            $query->where('is_active', true);
        }])
        ->where('category_name', '!=', 'Basic Information')
        ->get();
        
        // Transform the data to ensure proper structure for frontend
        $transformedCategories = $categories->map(function($category) {
            return [
                'id' => $category->id,
                'category_name' => $category->category_name,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
                'subCategories' => $category->subCategories->map(function($subCategory) {
                    return [
                        'id' => $subCategory->id,
                        'subcategory_name' => $subCategory->subcategory_name,
                        'category_id' => $subCategory->category_id,
                        'created_at' => $subCategory->created_at,
                        'updated_at' => $subCategory->updated_at,
                        'questions' => $subCategory->questions->map(function($question) {
                            return [
                                'id' => $question->id,
                                'question' => $question->question,
                                'access' => $question->access,
                                'is_active' => (bool) $question->is_active,
                                'subcategory_id' => $question->subcategory_id,
                                'created_at' => $question->created_at,
                                'updated_at' => $question->updated_at,
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ];
        });
        
        // Debug: Log the structure of the first category
        if ($transformedCategories->count() > 0) {
            $firstCategory = $transformedCategories->first();
            Log::info('First category structure:', [
                'id' => $firstCategory['id'],
                'name' => $firstCategory['category_name'],
                'subCategories_count' => count($firstCategory['subCategories']),
                'subCategories_key_exists' => isset($firstCategory['subCategories']),
                'first_subcategory_name' => $firstCategory['subCategories'][0]['subcategory_name'] ?? 'N/A'
            ]);
        }
        
        return response()->json($transformedCategories);
    }

    /**
     * Show HTE profile page
     */
    public function profile()
    {
        $user = Auth::user();
        $hte = $user->hte;
        
        if (!$hte) {
            return redirect()->route('form');
        }

        // If HTE hasn't submitted yet, show message to complete form first
        if (!$hte->is_submit) {
            return Inertia::render('hte/profile', [
                'hte' => null,
                'hasSubmitted' => false,
                'message' => 'No information yet, please answer the form first.'
            ]);
        }

        // Get HTE with related data
        $hteWithData = HTE::with([
            'internships.subcategoryWeights.subcategory'
        ])->find($hte->id);

        // Debug: Log the data being sent to the frontend
        Log::info('HTE Profile Data:', [
            'hte_id' => $hteWithData->id,
            'internships_count' => $hteWithData->internships ? $hteWithData->internships->count() : 0,
            'first_internship_subcategory_weights_count' => $hteWithData->internships && $hteWithData->internships->first() 
                ? ($hteWithData->internships->first()->subcategoryWeights ? $hteWithData->internships->first()->subcategoryWeights->count() : 0) 
                : 0
        ]);

        return Inertia::render('hte/profile', [
            'hte' => $hteWithData,
            'hasSubmitted' => true
        ]);
    }
}
