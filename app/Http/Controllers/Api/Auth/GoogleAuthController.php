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
    private const ALLOWED_GOOGLE_ISSUERS = [
        'accounts.google.com',
        'https://accounts.google.com',
    ];

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $allowedClientIds = $this->allowedGoogleClientIds();
        if (empty($allowedClientIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Google sign-in is not configured.',
            ], 503);
        }

        try {
            // Do not pin verification to one client_id so web/mobile tokens can all pass signature checks.
            // We enforce allowed audiences manually in isValidGooglePayload().
            $client = new GoogleClient;
            $payload = $client->verifyIdToken($request->input('id_token'));
        } catch (Throwable $e) {
            Log::warning('Google ID token verification failed', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => self::INVALID_GOOGLE_TOKEN_MESSAGE,
            ], 401);
        }

        if (! is_array($payload) || ! $this->isValidGooglePayload($payload, $allowedClientIds)) {
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

            $deviceName = trim((string) $request->input('device_name', 'google_auth'));
            $token = $user->createToken($deviceName !== '' ? $deviceName : 'google_auth')->plainTextToken;

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

    /**
     * Accepts old config key (client_id) and new key (client_ids array).
     *
     * @return array<int, string>
     */
    private function allowedGoogleClientIds(): array
    {
        $clientIds = config('services.google.client_ids', []);
        if (! is_array($clientIds)) {
            $clientIds = [];
        }

        $legacyClientId = config('services.google.client_id');
        if (is_string($legacyClientId) && trim($legacyClientId) !== '') {
            $clientIds[] = $legacyClientId;
        }

        return array_values(array_unique(array_filter(array_map(
            static fn ($id) => is_string($id) ? trim($id) : '',
            $clientIds
        ))));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $allowedClientIds
     */
    private function isValidGooglePayload(array $payload, array $allowedClientIds): bool
    {
        $audience = (string) ($payload['aud'] ?? '');
        $issuer = (string) ($payload['iss'] ?? '');
        $email = (string) ($payload['email'] ?? '');
        $emailVerified = filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $subject = (string) ($payload['sub'] ?? '');
        $expiry = (int) ($payload['exp'] ?? 0);

        return $audience !== ''
            && in_array($audience, $allowedClientIds, true)
            && in_array($issuer, self::ALLOWED_GOOGLE_ISSUERS, true)
            && $email !== ''
            && $emailVerified
            && $subject !== ''
            && $expiry > now()->timestamp;
    }
}
