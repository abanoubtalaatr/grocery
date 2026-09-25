<?php

namespace App\Actions\Auth;

use App\Services\AuthService;

class ResetPasswordAction
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function handle(array $data): void
    {
        $this->authService->resetPassword(
            $data['identifier'],
            $data['otp'],
            $data['password']
        );
    }
}