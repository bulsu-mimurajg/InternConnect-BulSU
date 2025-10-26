<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Question;
use App\Models\Category;
use App\Models\SubCategory;
use App\Services\DeadlineStatusService;
use Inertia\Inertia;
use Inertia\Response;

class QuestionController extends Controller
{
    /**
     * Display the forms management page
     */
    public function index(Request $request): Response
    {
        $query = Question::with(['subcategory.category', 'answers']);

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where('question', 'like', "%{$searchTerm}%");
        }

        // Apply category filter
        if ($request->filled('category_id')) {
            $categoryId = $request->get('category_id');
            $query->whereHas('subcategory', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        // Apply subcategory filter
        if ($request->filled('subcategory_id')) {
            $subcategoryId = $request->get('subcategory_id');
            $query->where('subcategory_id', $subcategoryId);
        }

        // Apply default sorting by creation date
        $query->orderBy('created_at', 'desc');

        $questions = $query->get();

        // Get categories with their subcategories and map the data structure
        $categories = Category::with('subCategories')
            ->orderBy('category_name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'category_name' => $category->category_name,
                    'subCategories' => $category->subCategories->map(function ($subcategory) {
                        return [
                            'id' => $subcategory->id,
                            'subcategory_name' => $subcategory->subcategory_name,
                        ];
                    }),
                ];
            });

        // Get all subcategories for the subcategory filter dropdown
        $subcategories = SubCategory::with('category')
            ->orderBy('subcategory_name')
            ->get()
            ->map(function ($subcategory) {
                return [
                    'id' => $subcategory->id,
                    'subcategory_name' => $subcategory->subcategory_name,
                    'category_id' => $subcategory->category_id,
                    'category_name' => $subcategory->category->category_name,
                ];
            });

        // Get deadline status for restrictions
        $deadlineStatusService = new DeadlineStatusService();
        $deadlineStatus = $deadlineStatusService->getAdminDeadlineStatus();

        return Inertia::render('admin/forms', [
            'questions' => $questions,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'filters' => [
                'search' => $request->get('search', ''),
                'category_id' => $request->get('category_id', ''),
                'subcategory_id' => $request->get('subcategory_id', ''),
            ],
            'deadlineStatus' => $deadlineStatus,
        ]);
    }

    /**
     * Store a newly created question
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'question' => 'required|string|max:1000',
            'question_type' => 'required|in:multiple_choice,true_false,essay,enumeration,identification',
            'points' => 'nullable|numeric|min:0|max:100',
            'subcategory_id' => 'required|exists:sub_categories,id',
            'answers' => 'required_unless:question_type,essay,enumeration|array|min:2',
            'answers.*.answer_text' => 'required_with:answers|string|max:500',
            'answers.*.is_correct' => 'required_with:answers|boolean',
            'answers.*.display_order' => 'required_with:answers|integer|min:1',
        ]);

        // Create the question
        $question = Question::create([
            'question' => $request->question,
            'question_type' => $request->question_type,
            'points' => $request->points ?? 1.00,
            'subcategory_id' => $request->subcategory_id,
            'is_active' => true,
        ]);

        // Create answers for objective questions
        if (in_array($request->question_type, ['multiple_choice', 'true_false', 'identification']) && $request->has('answers')) {
            foreach ($request->answers as $answerData) {
                $question->answers()->create([
                    'answer_text' => $answerData['answer_text'],
                    'is_correct' => $answerData['is_correct'] ?? false,
                    'display_order' => $answerData['display_order'] ?? 1,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Question created successfully!');
    }

    /**
     * Update the specified question
     */
    public function update(Request $request, Question $question): RedirectResponse
    {
        $request->validate([
            'question' => 'required|string|max:1000',
            'question_type' => 'required|in:multiple_choice,true_false,essay,enumeration,identification',
            'points' => 'nullable|numeric|min:0|max:100',
            'subcategory_id' => 'required|exists:sub_categories,id',
            'answers' => 'required_unless:question_type,essay,enumeration|array|min:2',
            'answers.*.answer_text' => 'required_with:answers|string|max:500',
            'answers.*.is_correct' => 'required_with:answers|boolean',
            'answers.*.display_order' => 'required_with:answers|integer|min:1',
        ]);

        // Update the question
        $question->update([
            'question' => $request->question,
            'question_type' => $request->question_type,
            'points' => $request->points ?? 1.00,
            'subcategory_id' => $request->subcategory_id,
        ]);

        // Update answers for objective questions
        if (in_array($request->question_type, ['multiple_choice', 'true_false', 'identification']) && $request->has('answers')) {
            // Delete existing answers
            $question->answers()->delete();
            
            // Create new answers
            foreach ($request->answers as $answerData) {
                $question->answers()->create([
                    'answer_text' => $answerData['answer_text'],
                    'is_correct' => $answerData['is_correct'] ?? false,
                    'display_order' => $answerData['display_order'] ?? 1,
                ]);
            }
        } else {
            // For essay/enumeration questions, remove existing answers
            $question->answers()->delete();
        }

        return redirect()->back()->with('success', 'Question updated successfully!');
    }

    /**
     * Archive the specified question
     */
    public function archive(Question $question): RedirectResponse
    {
        $question->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Question archived successfully!');
    }

    /**
     * Restore the specified question
     */
    public function restore(Question $question): RedirectResponse
    {
        $question->update(['is_active' => true]);

        return redirect()->back()->with('success', 'Question restored successfully!');
    }

    /**
     * Get subcategories for a specific category
     */
    public function getSubcategories(Category $category)
    {
        return response()->json($category->subCategories);
    }
}
