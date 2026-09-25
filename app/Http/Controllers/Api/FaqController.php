<?php
namespace App\Http\Controllers\Api;

use App\Actions\Faq\GetFaqsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use ResponseTrait;

    /**
     * Display a listing of the FAQs.
     */
    public function index(Request $request, GetFaqsAction $action): JsonResponse
    {
        $data = $action->execute($request);

        return $this->successResponse($data, 'FAQs retrieved successfully');
    }

    /**
     * Store a newly created FAQ.
     */
    public function store(StoreFaqRequest $request): JsonResponse
    {
        $faq = Faq::create($request->validated());

        return $this->successResponse(
            new FaqResource($faq),
            'FAQ created successfully',
            201
        );
    }

    /**
     * Display the specified FAQ.
     */
    public function show(Faq $faq): JsonResponse
    {
        return $this->successResponse(
            new FaqResource($faq),
            'FAQ retrieved successfully'
        );
    }

    /**
     * Update the specified FAQ.
     */
    public function update(UpdateFaqRequest $request, Faq $faq): JsonResponse
    {
        $faq->update($request->validated());

        return $this->successResponse(
            new FaqResource($faq),
            'FAQ updated successfully'
        );
    }

    /**
     * Remove the specified FAQ.
     */
    public function destroy(Faq $faq): JsonResponse
    {
        $faq->delete();

        return $this->successResponse(null, 'FAQ deleted successfully');
    }

    /**
     * Get all FAQ categories.
     */
    public function categories(): JsonResponse
    {
        $categories = Faq::active()
            ->distinct('category')
            ->pluck('category')
            ->filter()
            ->values();

        return $this->successResponse($categories, 'FAQ categories retrieved successfully');
    }

    /**
     * Get FAQs by category.
     */
    public function byCategory(string $category): JsonResponse
    {
        $faqs = Faq::active()
            ->category($category)
            ->ordered()
            ->get();

        return $this->successResponse(
            FaqResource::collection($faqs),
            'FAQs by category retrieved successfully'
        );
    }
}