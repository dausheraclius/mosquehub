<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Concerns\HasMosqueContext;
use App\Support\SiteContext;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    use HasMosqueContext;

    public function __construct()
    {
        $user = auth()->user();

        if (! $user || $user->role !== 'Ketua YMBPK') {
            abort(403, 'Anda tidak memiliki akses ke manajemen pengguna.');
        }

        $this->mosqueId = SiteContext::mosqueId();
    }

    public function index()
    {
        $userList = User::where('mosque_id', $this->mosqueId)
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => $this->toArrayForFrontend($u));

        return view('pages.pengaturan.user-management', [
            'userList' => $userList,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => 'nullable|string|max:30',
            'role' => 'required|string|max:100',
            'status' => 'required|in:aktif,nonaktif',
            'password' => 'required|string|min:8',
            'permissions' => 'nullable|array',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'status' => $validated['status'],
            'password' => $validated['password'],
            'mosque_id' => $this->mosqueId,
            'permissions' => $validated['permissions'] ?? null,
        ]);

        return response()->json($this->toArrayForFrontend($user));
    }

    public function update(Request $request, $id)
    {
        $user = User::where('mosque_id', $this->mosqueId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => 'nullable|string|max:30',
            'role' => 'required|string|max:100',
            'status' => 'required|in:aktif,nonaktif',
            'permissions' => 'nullable|array',
        ]);

        if ($user->id === $request->user()?->id && $validated['status'] === 'nonaktif') {
            return response()->json(['message' => 'Tidak bisa menonaktifkan akun sendiri.'], 422);
        }

        if ($user->role === 'Ketua YMBPK' && $validated['role'] !== 'Ketua YMBPK') {
            $this->guardLastActiveLeader($user, 'Tidak bisa menurunkan role ketua YMBPK terakhir yang aktif.');
        }

        if ($user->role === 'Ketua YMBPK' && $validated['status'] === 'nonaktif') {
            $this->guardLastActiveLeader($user, 'Tidak bisa menonaktifkan ketua YMBPK terakhir yang aktif.');
        }

        $user->update(collect($validated)->only(['name', 'email', 'phone', 'role', 'status', 'permissions'])->toArray());

        return response()->json($this->toArrayForFrontend($user));
    }

    public function updateStatus(Request $request, $id)
    {
        $user = User::where('mosque_id', $this->mosqueId)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:aktif,nonaktif',
        ]);

        if ($user->id === $request->user()?->id) {
            return response()->json(['message' => 'Tidak bisa menonaktifkan akun sendiri.'], 422);
        }

        if ($validated['status'] === 'nonaktif' && $user->role === 'Ketua YMBPK') {
            $this->guardLastActiveLeader($user, 'Tidak bisa menonaktifkan ketua YMBPK terakhir yang aktif.');
        }

        $user->update(['status' => $validated['status']]);

        return response()->json(['success' => true]);
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::where('mosque_id', $this->mosqueId)->findOrFail($id);

        $validated = $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $user->update(['password' => $validated['password']]);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, $id)
    {
        $user = User::where('mosque_id', $this->mosqueId)->findOrFail($id);

        if ($user->id === $request->user()?->id) {
            return response()->json(['message' => 'Tidak bisa menghapus akun sendiri.'], 422);
        }

        if ($user->role === 'Ketua YMBPK') {
            $this->guardLastActiveLeader($user, 'Tidak bisa menghapus ketua YMBPK terakhir yang aktif.');
        }

        $user->delete();

        return response()->json(['message' => 'User dihapus']);
    }

    private function guardLastActiveLeader(User $user, string $message): void
    {
        $activeLeaders = User::where('mosque_id', $this->mosqueId)
            ->where('role', 'Ketua YMBPK')
            ->where('status', 'aktif')
            ->count();

        if ($activeLeaders <= 1 && $user->status === 'aktif') {
            abort(422, $message);
        }
    }

    private function toArrayForFrontend(User $u): array
    {
        $roleMap = [
            'Ketua YMBPK' => 'ketua',
            'Sekretaris' => 'sekretaris',
            'Bendahara' => 'bendahara',
        ];
        $roleSlug = $roleMap[$u->role] ?? 'custom';

        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'phone' => $u->phone ?: '-',
            'role' => $u->role,
            'roleSlug' => $roleSlug,
            'status' => $u->status,
            'permissions' => $u->permissions ?: [],
            'lastLogin' => $u->last_login_at ? \Illuminate\Support\Carbon::parse($u->last_login_at)->diffForHumans() : 'Belum pernah login',
            'lastLoginTs' => $u->last_login_at ? \Illuminate\Support\Carbon::parse($u->last_login_at)->timestamp : 0,
        ];
    }
}
