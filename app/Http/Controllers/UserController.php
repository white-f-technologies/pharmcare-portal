<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $is_active = $request->status === 'active';
            $query->where('is_active', $is_active);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', Rule::in(['admin', 'pharmacist', 'cashier'])],
            'password' => ['required', 'confirmed', Password::defaults()],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $user = User::create($validated);

        ActivityLog::log(
            'User Created',
            "Created user {$user->name} ({$user->email}) with role {$user->role}",
            $user
        );

        return redirect()->route('users.index')
            ->with('success', "User account for {$user->name} created successfully.");
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', Rule::in(['admin', 'pharmacist', 'cashier'])],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Update password only if provided
        if ($request->filled('password')) {
            $user->password = $validated['password'];
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        ActivityLog::log(
            'User Updated',
            "Updated details/role for user {$user->name} ({$user->email})",
            $user
        );

        return redirect()->route('users.index')
            ->with('success', "User account for {$user->name} updated successfully.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account while logged in.');
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::log(
            'User Deleted',
            "Deleted user account {$name}"
        );

        return redirect()->route('users.index')
            ->with('success', "User account for {$name} deleted successfully.");
    }
}
