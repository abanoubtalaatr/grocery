<?php
namespace App\Actions\Faq;

use App\Http\Resources\FaqCollection;
use App\Models\Faq;
use Illuminate\Http\Request;

class GetFaqsAction
{
    public function execute(Request $request): array
    {
        $query = Faq::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question', 'LIKE', "%{$search}%")
                    ->orWhere('answer', 'LIKE', "%{$search}%");
            });
        }

        $query->ordered();

        $faqs = $query->paginate(15);

        $result = [
            'faqs' => new FaqCollection($faqs),
        ];

        if ($request->boolean('with_categories', false)) {
            $result['categories'] = Faq::active()
                ->distinct('category')
                ->pluck('category')
                ->filter()
                ->values();
        }

        return $result;
    }
}