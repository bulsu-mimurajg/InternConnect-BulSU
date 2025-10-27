<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\HTEQuestion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HTECriteriaController extends Controller
{
    /**
     * Display a listing of HTE criteria questions.
     */
    public function index(Request $request)
    {
        $query = HTEQuestion::with(['subcategory.category']);

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('subcategory', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Filter by subcategory
        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        } else {
            // Default: show only active questions
            $query->where('is_active', true);
        }

        // Search by question text
        if ($request->filled('search')) {
            $query->where('question', 'like', '%' . $request->search . '%');
        }

        $questions = $query->orderBy('created_at', 'desc')->paginate(15);

        $categories = Category::with('subCategories')->get();
        $subcategories = SubCategory::all();

        return Inertia::render('admin/hte-criteria', [
            'questions' => $questions,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'filters' => $request->only(['category_id', 'subcategory_id', 'status', 'search']),
        ]);
    }

    /**
     * Store a newly created HTE criteria question.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:5000',
            'subcategory_id' => 'required|exists:sub_categories,id',
            'is_active' => 'boolean',
        ]);

        HTEQuestion::create($validated);

        return redirect()->back()->with('success', 'HTE criteria question added successfully.');
    }

    /**
     * Update the specified HTE criteria question.
     */
    public function update(Request $request, HTEQuestion $hteQuestion)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:5000',
            'subcategory_id' => 'required|exists:sub_categories,id',
            'is_active' => 'boolean',
        ]);

        $hteQuestion->update($validated);

        return redirect()->back()->with('success', 'HTE criteria question updated successfully.');
    }

    /**
     * Archive (soft delete) the specified HTE criteria question.
     */
    public function archive(HTEQuestion $hteQuestion)
    {
        $hteQuestion->update(['is_active' => false]);

        return redirect()->back()->with('success', 'HTE criteria question archived successfully.');
    }

    /**
     * Restore the specified HTE criteria question.
     */
    public function restore(HTEQuestion $hteQuestion)
    {
        $hteQuestion->update(['is_active' => true]);

        return redirect()->back()->with('success', 'HTE criteria question restored successfully.');
    }

    /**
     * Get subcategories for a specific category.
     */
    public function getSubcategories(Category $category)
    {
        return response()->json($category->subcategories);
    }
}

