<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use App\Http\Resources\FaqCollection;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    /**
     * Display a listing of the FAQs.
     */
    public function index(Request $request)
    {
        $query = Faq::query();

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter active only
        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        // Search in question and answer
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('question', 'LIKE', "%{$search}%")
                  ->orWhere('answer', 'LIKE', "%{$search}%");
            });
        }

        // Order by
        $query->ordered();

        // Get categories list
        if ($request->boolean('with_categories', false)) {
            $categories = Faq::active()
                ->distinct('category')
                ->pluck('category')
                ->filter()
                ->values();
        }

        $perPage = $request->get('per_page', 15);
        $faqs = $query->paginate($perPage);

        $response = [
            'data' => new FaqCollection($faqs)
        ];

        if ($request->boolean('with_categories', false)) {
            $response['categories'] = $categories;
        }

        return response()->json($response);
    }

    /**
     * Store a newly created FAQ.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'category' => 'nullable|string|max:100',
            'order' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $faq = Faq::create($validator->validated());

        return response()->json([
            'message' => 'FAQ created successfully',
            'data' => new FaqResource($faq)
        ], 201);
    }

    /**
     * Display the specified FAQ.
     */
    public function show(Faq $faq)
    {
        return new FaqResource($faq);
    }

    /**
     * Update the specified FAQ.
     */
    public function update(Request $request, Faq $faq)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'sometimes|required|string|max:255',
            'answer' => 'sometimes|required|string',
            'category' => 'nullable|string|max:100',
            'order' => 'nullable|integer',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $faq->update($validator->validated());

        return response()->json([
            'message' => 'FAQ updated successfully',
            'data' => new FaqResource($faq)
        ]);
    }

    /**
     * Remove the specified FAQ.
     */
    public function destroy(Faq $faq)
    {
        $faq->delete();

        return response()->json([
            'message' => 'FAQ deleted successfully'
        ]);
    }

    /**
     * Get all FAQ categories.
     */
    public function categories()
    {
        $categories = Faq::active()
            ->distinct('category')
            ->pluck('category')
            ->filter()
            ->values();

        return response()->json([
            'data' => $categories
        ]);
    }

    /**
     * Get FAQs by category.
     */
    public function byCategory($category)
    {
        $faqs = Faq::active()
            ->category($category)
            ->ordered()
            ->get();

        return FaqResource::collection($faqs);
    }
}