<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::query()->orderBy('id')->paginate(15));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $validated['is_active'] ?? true;
        $user = User::create($validated);

        return response()->json(['message' => 'User created successfully.', 'data' => $user], 201);
    }

    public function show(User $user)
    {
        return response()->json(['data' => $user->load('orders')]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
        ]);
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }
        $user->update($validated);

        return response()->json(['message' => 'User updated successfully.', 'data' => $user->fresh()]);
    }

    public function updateStatus(Request $request, User $user)
    {
        $validated = $request->validate(['is_active' => ['required', 'boolean']]);
        $user->update(['is_active' => $validated['is_active']]);

        return response()->json(['message' => 'User status updated successfully.', 'data' => $user->fresh()]);
    }
}
