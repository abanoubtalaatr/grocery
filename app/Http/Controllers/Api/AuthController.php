<?php

namespace App\Http\Controllers\Api;
use App\Http\Resources\Api\UserResource;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->success('Registration successful', 201, [
            'user'  => UserResource::make($result['user']),
            'token' => $result['token'],
        ]);
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->input('login'),
            $request->input('password')
        );

        return $this->success('Login successful', 200, [
            'user'  => UserResource::make($result['user']),
            'token' => $result['token'],
        ]);
    }
    /**
     * Logout user
     */
   public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success('Logout successful', 200);
    }
  
    /**
     * Forgot password - send OTP
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->input('identifier'));

        return $this->success('OTP sent successfully. Please check your email or phone.', 200);
    }

   /**
     * Verify OTP
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $isValid = $this->authService->verifyOtp(
            $request->input('identifier'),
            $request->input('otp')
        );

        if (! $isValid) {
            return $this->error('Invalid or expired OTP', 400);
        }

        return $this->success('OTP verified successfully', 200);
    }

    /**
     * Reset password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword(
            $request->input('identifier'),
            $request->input('otp'),
            $request->input('password')
        );

        return $this->success('Password reset successfully', 200);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        return $this->success('User profile retrieved successfully', 200, [
            'user' => UserResource::make($request->user()),
        ]);
    }

    public function deleteAccount(DeleteAccountRequest $request): JsonResponse
    {
        $this->authService->deleteAccount($request->user());

        return $this->success('Account deleted successfully', 200);
    }

    /**
     * Change password for authenticated user
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update([
            'password' => $request->input('password'),
        ]);

        return $this->success('Password changed successfully', 200);
    }
}