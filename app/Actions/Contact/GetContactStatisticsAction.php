<?php
namespace App\Actions\Contact;

use App\Models\ContactMessage;

class GetContactStatisticsAction
{
    public function execute(): array
    {
        return [
            'total' => ContactMessage::count(),
            'new' => ContactMessage::new()->count(),
            'read' => ContactMessage::read()->count(),
            'replied' => ContactMessage::replied()->count(),
            'spam' => ContactMessage::spam()->count(),
            'monthly_stats' => ContactMessage::selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as total,
                SUM(CASE WHEN status = "new" THEN 1 ELSE 0 END) as new,
                SUM(CASE WHEN status = "replied" THEN 1 ELSE 0 END) as replied
            ')
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('month')
                ->orderBy('month')
                ->get(),
        ];
    }
}