<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Portfolio;
use App\Models\Institution;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'role' => 'required|in:user,institution,employer',
            'region' => 'sometimes|string',
            'city' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'region' => $request->region,
                'city' => $request->city,
                'status' => 'active',
            ]);

            // Create portfolio for users
            if ($user->role === 'user') {
                Portfolio::create([
                    'user_id' => $user->id,
                    'visibility' => 'public'
                ]);
            }

            // Log registration
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'register',
                'entity_type' => 'user',
                'entity_id' => $user->id,
                'new_data' => ['role' => $user->role],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
                'message' => 'Registration successful.'
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->login)
                    ->orWhere('phone', $request->login)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Login successful'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}