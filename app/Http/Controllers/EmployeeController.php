<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    private const ROLES = [
        'Administrator',
        'Sales',
        'Purchasing',
        'Warehouse',
        'Route',
    ];

    public function index()
    {
        return User::query()
            ->select('id', 'name', 'email', 'role', 'is_active', 'created_at')
            ->orderBy('name')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(self::ROLES)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $employee = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => $data['password'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Empleado registrado correctamente.',
            'employee' => $this->employeeData($employee),
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(self::ROLES)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $this->protectLastAdministrator($user, $data['role'], $data['is_active'] ?? $user->is_active);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        if (array_key_exists('is_active', $data)) {
            $user->is_active = $data['is_active'];
        }
        $user->save();

        return response()->json([
            'message' => 'Empleado actualizado correctamente.',
            'employee' => $this->employeeData($user->fresh()),
        ]);
    }

    public function updateStatus(Request $request, User $user)
    {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        if ($user->is(auth()->user()) && ! $data['is_active']) {
            throw ValidationException::withMessages([
                'is_active' => 'No puedes desactivar tu propia cuenta mientras tienes la sesión iniciada.',
            ]);
        }

        $this->protectLastAdministrator($user, $user->role, $data['is_active']);
        $user->is_active = $data['is_active'];
        $user->save();

        return response()->json([
            'message' => $user->is_active ? 'Empleado activado.' : 'Empleado desactivado.',
            'employee' => $this->employeeData($user),
        ]);
    }

    private function protectLastAdministrator(User $user, string $newRole, bool $willBeActive): void
    {
        $isRemovingAdmin = $user->role === 'Administrator'
            && $user->is_active
            && ($newRole !== 'Administrator' || ! $willBeActive);

        if (! $isRemovingAdmin) {
            return;
        }

        $activeAdministrators = User::where('role', 'Administrator')
            ->where('is_active', true)
            ->count();

        if ($activeAdministrators <= 1) {
            throw ValidationException::withMessages([
                'role' => 'HALCON debe conservar al menos un administrador activo.',
            ]);
        }
    }

    private function employeeData(User $user): array
    {
        return $user->only(['id', 'name', 'email', 'role', 'is_active', 'created_at']);
    }
}
