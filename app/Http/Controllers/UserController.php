<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        // Build query with filters
        $query = User::query();
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Apply role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        // Get paginated results (10 per page)
        $users = $query->latest()->paginate(10);
        
        return Inertia::render('User', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,user',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('user.index')->with('success', 'User created successfully!');
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        // Check if trying to update the first admin (ID 1)
        if ($user->id === 1) {
            return redirect()->route('user.index')->with('error', 'You cannot modify the primary admin account!');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,user',
        ]);

        // Prepare data for update - explicitly include role
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        // Only update password if provided
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        // Update the user with prepared data
        $user->update($updateData);

        return redirect()->route('user.index')->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('user.index')->with('error', 'You cannot delete your own account!');
        }
        
        // Prevent deleting the first admin (ID 1)
        if ($user->id === 1) {
            return redirect()->route('user.index')->with('error', 'You cannot delete the primary admin account!');
        }

        $user->delete();

        return redirect()->route('user.index')->with('success', 'User deleted successfully!');
    }
}