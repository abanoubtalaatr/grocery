<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;

use Google\Client as GoogleClient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'id_token' => 'required'
        ]);

        $client = new GoogleClient([
            'client_id' => config('services.google.client_id')
        ]);

        $payload = $client->verifyIdToken($request->id_token);

        if (!$payload) {
            return response()->json(['message' => 'Invalid Google token'], 401);
        }
        

        $user = User::updateOrCreate(
            ['email' => $payload['email']],
            [
                'google_id' => $payload['sub'],
                'name' => $payload['name'],
                'username' => Str::slug($payload['name']),
                'avatar' => $payload['picture'],
                'password' => bcrypt(Str::random(16))
            ]
        );

        // JWT Example
        $token = auth()->login($user);

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }
}
