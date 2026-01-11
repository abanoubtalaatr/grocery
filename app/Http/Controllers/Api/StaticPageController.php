<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StaticPageResource;
use App\Http\Resources\StaticPageCollection;
use App\Models\StaticPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StaticPageController extends Controller
{
    /**
     * Display a listing of static pages.
     */
    public function index(Request $request)
    {
        $query = StaticPage::query();

        // Show only published pages for non-admin users
        if (!$request->user() || !$request->user()->is_admin) {
            $query->published();
        }

        // Filter by published status
        if ($request->has('published')) {
            $query->where('is_published', $request->boolean('published'));
        }

        // Search in title and content
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%");
            });
        }

        $query->ordered();

        $perPage = $request->get('per_page', 20);
        $pages = $query->paginate($perPage);

        return new StaticPageCollection($pages);
    }

    /**
     * Store a newly created static page.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required|string|unique:static_pages,slug|max:100',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
            'is_published' => 'boolean',
            'order' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $page = StaticPage::create($validator->validated());

        return response()->json([
            'message' => 'Page created successfully',
            'data' => new StaticPageResource($page)
        ], 201);
    }

    /**
     * Display the specified static page by slug.
     */
    public function showBySlug($slug)
    {
        $page = StaticPage::bySlug($slug)->first();

        if (!$page) {
            return response()->json([
                'message' => 'Page not found'
            ], 404);
        }

        // Check if page is published for non-admin users
        if (!$page->is_published && (!request()->user() || !request()->user()->is_admin)) {
            return response()->json([
                'message' => 'Page not found'
            ], 404);
        }

        return new StaticPageResource($page);
    }

    /**
     * Display the specified static page by ID.
     */
    public function show(StaticPage $staticPage)
    {
        return new StaticPageResource($staticPage);
    }

    /**
     * Update the specified static page.
     */
    public function update(Request $request, StaticPage $staticPage)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'sometimes|required|string|max:100|unique:static_pages,slug,' . $staticPage->id,
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
            'is_published' => 'sometimes|boolean',
            'order' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $staticPage->update($validator->validated());

        return response()->json([
            'message' => 'Page updated successfully',
            'data' => new StaticPageResource($staticPage)
        ]);
    }

    /**
     * Remove the specified static page.
     */
    public function destroy(StaticPage $staticPage)
    {
        $staticPage->delete();

        return response()->json([
            'message' => 'Page deleted successfully'
        ]);
    }

    /**
     * Get important pages (for footer/menu).
     */
    public function importantPages()
    {
        $pages = StaticPage::published()
            ->whereIn('slug', ['terms-and-conditions', 'privacy-policy', 'about-us', 'contact-us'])
            ->ordered()
            ->get(['slug', 'title']);

        return response()->json([
            'data' => $pages
        ]);
    }
}