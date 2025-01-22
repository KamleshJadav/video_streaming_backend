<?php
// UserController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
class UserController extends Controller
{
    // Registration API
    public function register(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'last_name' => 'nullable|string|max:50000',
                'first_name' => 'nullable|string|max:50000',
                'role' => 'nullable|string|in:admin,mobile_user,web_user',
            ]);

            // Role validation (if role is admin)
            if ($request->role === 'admin') {
                $existingAdmin = User::where('role', 'admin')->first();
                if ($existingAdmin) {
                    return response()->json([
                        'success' => false,
                        'message' => 'An admin user already exists',
                    ], 200);
                }
            }

            // Create user
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'last_name' => $request->last_name,
                'first_name' => $request->first_name,
                'role' => $request->role ?? 'web_user', // Default to 'web_user' if no role is provided
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to process request',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Check if user exists
            $user = User::where('email', $request->email)->first();
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'test' => Hash::check($request->password, $user->password),
                    'message' => 'Invalid credentials',
                ], 200);
            }

           
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to process request',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkAdmin(Request $request)
    {
        try {
            $admin = User::where('role', 'admin')->first(); 
            if ($admin !== null) {
                return response()->json([
                    'success' => true,
                    'isAdmin' => true,  
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'isAdmin' => false,  
                    'error' => 'Failed to process request',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'isAdmin' => false,  
                'error' => 'Failed to process request',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Update an existing actor
    public function update(Request $request)
    {
        try {

            // Validate incoming request
            $request->validate([
                'id' => 'required|integer|exists:users,id',
                'email' => [
                'required',
                'email',
                    Rule::unique('users', 'email')->ignore($request->id),
                ],
                'last_name' => 'nullable|string|max:50000',
                'first_name' => 'nullable|string|max:50000',
                'role' => 'nullable|string|in:admin,mobile_user,web_user',
            ]);

          

            // Find the actor by ID
            $actor = User::findOrFail($request->id);

            // Update actor record
            $actor->update([
                "email" =>$request->email,
                "last_name" =>$request->last_name,
                "first_name" =>$request->first_name,
                "role" =>$request->role,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $actor
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update user',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function changePassword(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'email' => 'required|email|exists:users,email', 
                'id' => 'required|integer|exists:users,id',
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6',
            ]);

            $user = User::findOrFail($request->id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 500);
            }
            
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect',
                ], 500);
            }
          
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return response()->json([
                'success' => true,
                'data' => $request->new_password,
                'message' => 'Password updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to change password',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function saveFcmToken(Request $request)
    {
        try {
            // Validate the incoming request
            $request->validate([
                'user_id' => 'required|integer|exists:users,id', 
                'fcm_token' => 'required|string', 
            ]);

            // Find the user by the provided ID
            $user = User::findOrFail($request->user_id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save FCM token',
                ], 500);
            }

            $user->fcm_token = $request->fcm_token;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'FCM token saved successfully.',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to save FCM token',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Get paginated actors (latest first)
    public function getPaginated(Request $request)
    {
        $page = $request->get('page', 1); 
        $perPage = $request->get('pageSize', 7); 

        $user = User::orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'User fetched successfully',
            'current_page' => $user->currentPage(),
            'data' => $user->items(),
            'total_records' => $user->total()
        ]);
    }

}
