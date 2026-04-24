<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Google\Client as GoogleClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GoogleAuthController extends Controller
{
    private const INVALID_GOOGLE_TOKEN_MESSAGE = 'Invalid Google token.';

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        $clientId = config('services.google.client_id');
        if (empty($clientId)) {
            return response()->json([
                'success' => false,
                'message' => 'Google sign-in is not configured.',
            ], 503);
        }

        try {
            $client = new GoogleClient(['client_id' => $clientId]);
            $payload = $client->verifyIdToken($request->input('id_token'));
        } catch (Throwable $e) {
            Log::warning('Google ID token verification failed', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => self::INVALID_GOOGLE_TOKEN_MESSAGE,
            ], 401);
        }

        if (! is_array($payload) || empty($payload['email'])) {
            return response()->json([
                'success' => false,
                'message' => self::INVALID_GOOGLE_TOKEN_MESSAGE,
            ], 401);
        }

        try {
            $email = strtolower((string) $payload['email']);

            $user = User::where('email', $email)->first();

            if ($user) {
                if (! $user->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your account has been deactivated.',
                    ], 403);
                }

                $user->fill([
                    'google_id' => $payload['sub'] ?? $user->google_id,
                    'avatar' => $payload['picture'] ?? $user->avatar,
                ]);

                if (! $user->email_verified) {
                    $user->email_verified = true;
                    $user->email_verified_at = now();
                }

                $user->save();
            } else {
                $user = User::create([
                    'username' => $this->uniqueUsernameForGoogle($payload, $email),
                    'email' => $email,
                    'google_id' => $payload['sub'] ?? null,
                    'avatar' => $payload['picture'] ?? null,
                    'password' => Str::random(32),
                    'agree_terms' => true,
                    'email_verified' => true,
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]);
            }

            $token = $user->createToken('google_auth')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'phone' => $user->phone,
                    ],
                    'token' => $token,
                ],
            ]);
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Wrong number of segments')
                || str_contains($msg, 'JWT')
                || str_contains($msg, 'jwt')) {
                return response()->json([
                    'success' => false,
                    'message' => self::INVALID_GOOGLE_TOKEN_MESSAGE,
                ], 401);
            }

            Log::error('Google login failed', [
                'message' => $msg,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Google sign-in failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Non-empty, unique username for new Google users (slug can be empty for non-Latin names).
     */
    private function uniqueUsernameForGoogle(array $payload, string $email): string
    {
        $fromName = Str::slug((string) ($payload['name'] ?? ''));
        $fromEmail = Str::slug(Str::before($email, '@'));
        $base = 'user';
        if ($fromName !== '') {
            $base = $fromName;
        } elseif ($fromEmail !== '') {
            $base = $fromEmail;
        }
        $base = Str::limit($base, User::USERNAME_MAX_LENGTH - 4, '');
        if ($base === '') {
            $base = 'user';
        }

        $candidate = Str::limit($base, User::USERNAME_MAX_LENGTH, '');
        if (! preg_match('/\p{L}/u', $candidate)) {
            $candidate = Str::limit('user_'.$candidate, User::USERNAME_MAX_LENGTH, '');
        }
        $n = 0;
        while (User::withTrashed()->where('username', $candidate)->exists()) {
            $n++;
            $suffix = (string) $n;
            $candidate = Str::limit($base, User::USERNAME_MAX_LENGTH - strlen($suffix), '').$suffix;
        }

        return Str::limit($candidate, User::USERNAME_MAX_LENGTH, '');
    }
}
