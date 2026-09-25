<?php
namespace App\Actions\Auth;

use App\Services\AuthService;

class RegisterUserAction
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function handle(array $data): array
    {
        return $this->authService->register($data);
    }
}