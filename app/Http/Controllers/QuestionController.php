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
        $query = Question::with(['subcategory.category']);

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

        $questions = $query->with('choices')->get();

        // Get categories with their subcategories and map the data structure
        // Order by ID to show in the order they were added
        $categories = Category::with(['subCategories' => function ($query) {
            $query->orderBy('id'); // Maintain order of subcategories within each category
        }])
            ->orderBy('id')
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
        // Order by ID to show in the order they were added
        $subcategories = SubCategory::with('category')
            ->orderBy('id')
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
            'subcategory_id' => 'required|exists:sub_categories,id',
            'question_type' => 'required|in:quiz',
            'choices' => 'required|array|min:2',
            'choices.*.choice_text' => 'required|string|max:500',
            'choices.*.is_correct' => 'required|boolean',
        ]);

        $question = Question::create([
            'question' => $request->question,
            'subcategory_id' => $request->subcategory_id,
            'question_type' => 'quiz',
            'is_active' => true,
        ]);

        // Create choices
        foreach ($request->choices as $choiceData) {
            \App\Models\Choice::create([
                'question_id' => $question->id,
                'choice_text' => $choiceData['choice_text'],
                'is_correct' => $choiceData['is_correct'] ?? false,
            ]);
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
            'subcategory_id' => 'required|exists:sub_categories,id',
            'question_type' => 'required|in:quiz',
            'choices' => 'required|array|min:2',
            'choices.*.choice_text' => 'required|string|max:500',
            'choices.*.is_correct' => 'required|boolean',
        ]);

        $question->update([
            'question' => $request->question,
            'subcategory_id' => $request->subcategory_id,
            'question_type' => 'quiz',
        ]);

        // Delete existing choices and create new ones
        $question->choices()->delete();
        
        foreach ($request->choices as $choiceData) {
            \App\Models\Choice::create([
                'question_id' => $question->id,
                'choice_text' => $choiceData['choice_text'],
                'is_correct' => $choiceData['is_correct'] ?? false,
            ]);
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
