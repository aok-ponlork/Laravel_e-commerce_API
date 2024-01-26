<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\Payment;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
//Role 1 = user:admin, 2 = user:reporter, 3 = usser:normal, 4 = user:specail
class AuthController extends Controller
{
    use HttpResponses;

    function login(LoginUserRequest $request)
    {
        // Validate the incoming request
        $request->validated();

        // Attempt to authenticate the user
        if (!Auth::attempt($request->only(['email', 'password']))) {
            return $this->error('', 'Credentials do not match', 401);
        }

        // Get the authenticated user
        $user = User::where('email', $request->email)->first();

        // Retrieve the roles associated with the user
        $roles = $user->roles()->pluck('role_name')->toArray();

        // Initialize empty array for abilities
        $abilities = [];
        $expiration = 60; // Default expiration time

        // Iterate through roles to set abilities and adjust expiration if the user is an admin
        foreach ($roles as $role) {
            $abilities[] = $role;
            // Check if the user has admin role
            if ($role == 'user:admin') {
                $expiration = 90; // Update expiration for admin users
            }
        }

        // Create a token with user abilities and set expiration
        $token = $user->createToken('API Token of ' . $user->name, $abilities, now()->addDays($expiration))->plainTextToken;

        // Return success response with user details and token
        return $this->success([
            'user' => $user,
            'token' => 'Bearer ' . $token
        ], "Congrats on successfully Login!");
    }

    public function register(StoreUserRequest $request)
    {
        // Validate the incoming request
        $validatedData = $request->validated();

        // Create a new user
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        // Attach roles and set timestamps for the pivot table
        $roles = [1, 2, 3]; // Adjust this array to include the appropriate role IDs
        $now = now()->toDateTimeString();

        // Attach roles with specified timestamps
        $user->roles()->attach(3, ['created_at' => $now, 'updated_at' => $now]);

        // Generate a token with a 60-day expiration
        $token = $user->createToken('API Token of ' . $user->name, ['user:normal'], now()->addDays(60))->plainTextToken;

        // Return a success response with user details and token
        return $this->success([
            'user' => $user,
            'token' => 'Bearer ' . $token
        ], "Congratulations on successfully registering!");
    }


    public function logout()
    {
        $user = Auth::user();
        $currentAccessToken = $user->currentAccessToken();
        if ($currentAccessToken) {
            $currentAccessToken->delete();
            return $this->success('', 'You have been successfully logged out, and your token has been deleted', 200);
        }
        return $this->error('', 'Logout failed. Token not found.', 400);
    }


    public function getUserProfile(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->error('', 'Oops, something went wrong!', 400);
        }
        $user_id = $user->user_id;
        //Apply the where condition before calling sum
        $totalSpend = Payment::where('user_id', $user_id)->sum('amount');

        return response()->json([
            'user_id' => $user->user_id,
            'user_name' => $user->name,
            'created_at' => $user->created_at,
            'user_email' => $user->email,
            'total_spend' => $totalSpend,
        ], 200);
    }

    public function checkToken()
    {
        $user = Auth::user();
        if ($user) {
            // User is authenticated, token is valid
            return response()->json(['message' => 'Token is valid'], 200);
        } else {
            // User is not authenticated, token is either invalid or expired
            return response()->json(['message' => 'Token is invalid or expired'], 401);
        }
    }
}
