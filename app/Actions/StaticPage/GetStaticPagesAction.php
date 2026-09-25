<?php

namespace App\Actions\StaticPage;

use App\Models\StaticPage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class GetStaticPagesAction
{
    public function execute(Request $request): LengthAwarePaginator
    {
        $query = StaticPage::query();

        if (!$request->user() || !$request->user()->is_admin) {
            $query->published();
        }

        if ($request->has('published')) {
            $query->where('is_published', $request->boolean('published'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%");
            });
        }

        return $query->ordered()->paginate(20);
    }
}