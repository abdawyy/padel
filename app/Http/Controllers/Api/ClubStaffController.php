<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StaffMemberResource;
use App\Models\Club;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClubStaffController extends Controller
{
    public function index(Request $request, Club $club)
    {
        $this->authorizeManagement($request->user(), $club);

        $members = $club->users()
            ->select('users.*')
            ->withPivot('role')
            ->orderBy('users.name')
            ->get();

        return StaffMemberResource::collection($members);
    }

    public function store(Request $request, Club $club): StaffMemberResource|JsonResponse
    {
        $this->authorizeManagement($request->user(), $club);

        $allowedRoles = $request->user()?->isSuperAdmin()
            ? ['super_admin', 'admin', 'manager', 'coach', 'staff', 'player']
            : ['admin', 'manager', 'coach', 'staff', 'player'];

        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'name' => ['required_without:user_id', 'string', 'max:255'],
            'email' => ['required_without:user_id', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'in:'.implode(',', $allowedRoles)],
            'club_role' => ['nullable', 'in:owner,manager,staff'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $member = DB::transaction(function () use ($validated, $club) {
            $user = ! empty($validated['user_id'])
                ? User::query()->findOrFail($validated['user_id'])
                : User::query()->firstOrNew(['email' => $validated['email']]);

            $user->fill([
                'name' => $validated['name'] ?? $user->name,
                'email' => $validated['email'] ?? $user->email,
                'phone' => $validated['phone'] ?? $user->phone,
                'role' => $validated['role'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            if (! empty($validated['password'])) {
                $user->password = $validated['password'];
            }

            if (! $user->exists && empty($validated['password'])) {
                $user->password = 'password123';
            }

            $user->save();

            $clubRole = $validated['club_role'] ?? $this->defaultClubRole($validated['role']);

            if ($club->users()->where('users.id', $user->id)->exists()) {
                $club->users()->updateExistingPivot($user->id, ['role' => $clubRole]);
            } else {
                $club->users()->attach($user->id, ['role' => $clubRole]);
            }

            return $club->users()->where('users.id', $user->id)->firstOrFail();
        });

        return new StaffMemberResource($member);
    }

    public function update(Request $request, Club $club, User $user): StaffMemberResource|JsonResponse
    {
        $this->authorizeManagement($request->user(), $club);

        if (! $club->users()->where('users.id', $user->id)->exists()) {
            return response()->json(['message' => 'This user is not assigned to the selected club.'], 404);
        }

        $allowedRoles = $request->user()?->isSuperAdmin()
            ? ['super_admin', 'admin', 'manager', 'coach', 'staff', 'player']
            : ['admin', 'manager', 'coach', 'staff', 'player'];

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['sometimes', 'in:'.implode(',', $allowedRoles)],
            'club_role' => ['sometimes', 'in:owner,manager,staff'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $user->fill([
            'name' => $validated['name'] ?? $user->name,
            'phone' => array_key_exists('phone', $validated) ? $validated['phone'] : $user->phone,
            'role' => $validated['role'] ?? $user->role,
            'is_active' => $validated['is_active'] ?? $user->is_active,
        ]);

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        if (! empty($validated['club_role'])) {
            $club->users()->updateExistingPivot($user->id, ['role' => $validated['club_role']]);
        }

        return new StaffMemberResource($club->users()->where('users.id', $user->id)->firstOrFail());
    }

    public function destroy(Request $request, Club $club, User $user): JsonResponse
    {
        $this->authorizeManagement($request->user(), $club);

        $pivotRole = $club->users()
            ->where('users.id', $user->id)
            ->first()?->pivot?->role;

        if ($pivotRole === 'owner') {
            return response()->json(['message' => 'The club owner cannot be removed from the club team.'], 422);
        }

        $club->users()->detach($user->id);

        return response()->json(['message' => 'Staff member removed successfully.']);
    }

    private function authorizeManagement(?User $user, Club $club): void
    {
        abort_unless($user?->canManageClub($club), 403, 'You are not allowed to manage club staff.');
    }

    private function defaultClubRole(string $role): string
    {
        return in_array($role, ['super_admin', 'admin', 'manager'], true) ? 'manager' : 'staff';
    }
}
