<?php

namespace App\Http\Controllers\Api;

use App\Actions\StaticPage\GetStaticPagesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaticPageRequest;
use App\Http\Requests\UpdateStaticPageRequest;
use App\Http\Resources\StaticPageCollection;
use App\Http\Resources\StaticPageResource;
use App\Models\StaticPage;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaticPageController extends Controller
{
    use ResponseTrait;

    /**
     * Display a listing of static pages.
     */
    public function index(Request $request, GetStaticPagesAction $action): JsonResponse
    {
        $pages = $action->execute($request);

        return $this->successResponse(
            new StaticPageCollection($pages),
            'Static pages retrieved successfully'
        );
    }

    /**
     * Store a newly created static page.
     */
    public function store(StoreStaticPageRequest $request): JsonResponse
    {
        $page = StaticPage::create($request->validated());

        return $this->successResponse(
            new StaticPageResource($page),
            'Page created successfully',
            201
        );
    }

    /**
     * Display the specified static page by slug.
     */
    public function showBySlug(Request $request, string $slug): JsonResponse
    {
        $page = StaticPage::bySlug($slug)->first();

        if (!$page) {
            return $this->errorResponse('Page not found', 404);
        }

        if (!$page->is_published && (!$request->user() || !$request->user()->is_admin)) {
            return $this->errorResponse('Page not found', 404);
        }

        return $this->successResponse(
            new StaticPageResource($page),
            'Page retrieved successfully'
        );
    }

    /**
     * Display the specified static page by ID.
     */
    public function show(StaticPage $staticPage): JsonResponse
    {
        return $this->successResponse(
            new StaticPageResource($staticPage),
            'Page retrieved successfully'
        );
    }

    /**
     * Update the specified static page.
     */
    public function update(UpdateStaticPageRequest $request, StaticPage $staticPage): JsonResponse
    {
        $staticPage->update($request->validated());

        return $this->successResponse(
            new StaticPageResource($staticPage),
            'Page updated successfully'
        );
    }

    /**
     * Remove the specified static page.
     */
    public function destroy(StaticPage $staticPage): JsonResponse
    {
        $staticPage->delete();

        return $this->successResponse(null, 'Page deleted successfully');
    }

    /**
     * Get important pages (for footer/menu).
     */
    public function importantPages(): JsonResponse
    {
        $pages = StaticPage::published()
            ->whereIn('slug', ['terms-and-conditions', 'policies', 'about-us', 'contact-us'])
            ->ordered()
            ->get(['slug', 'title']);

        return $this->successResponse($pages, 'Important pages retrieved successfully');
    }
}