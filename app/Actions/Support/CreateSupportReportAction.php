<?php

namespace App\Actions\Support;

use App\Models\SupportReport;
use App\Models\User;

class CreateSupportReportAction
{
    /**
     * Create a support report for the given user.
     */
    public function execute(User $user, array $data, ?string $ip, ?string $userAgent): SupportReport
    {
        return SupportReport::create([
            'user_id'      => $user->id,
            'issue_type'   => trim($data['issue_type']),
            'order_number' => ! empty($data['order_number']) ? trim($data['order_number']) : null,
            'message'      => trim($data['message']),
            'ip_address'   => $ip,
            'user_agent'   => $userAgent,
        ]);
    }
}