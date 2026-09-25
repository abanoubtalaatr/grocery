<?php
namespace App\Actions\Contact;

use App\Models\ContactMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class GetContactMessagesAction
{
    public function execute(Request $request): LengthAwarePaginator
    {
        return ContactMessage::query()
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('from_date'), fn($q) => $q->whereDate('created_at', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn($q) => $q->whereDate('created_at', '<=', $request->to_date))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(fn($sub) => $sub->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('subject', 'LIKE', "%{$search}%")
                    ->orWhere('message', 'LIKE', "%{$search}%"));
            })
            ->orderBy('sort_order', 'desc')
            ->paginate( 20);
    }
}