<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\AuthServiceInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserManagementController extends Controller
{
    protected AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'bannedBy']);

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->has('role') && $request->role) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Status filter
        if ($request->has('status') && $request->status !== '') {
            if ($request->status === 'active') {
                $query->where('is_active', true)->where('is_banned', false);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'banned') {
                $query->where('is_banned', true);
            }
        }

        $users = $query->paginate(15);

        // Get available roles for filter
        $roles = \Spatie\Permission\Models\Role::all();

        return Inertia::render('Admin/UserManagement', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search', 'role', 'status'])
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['roles', 'permissions', 'bannedBy']);

        return Inertia::render('Admin/UserDetails', [
            'user' => $user
        ]);
    }

    /**
     * Ban user
     */
    public function banUser(Request $request, User $user)
    {
        try {
            // Check if trying to ban super admin
            if ($user->hasRole('super-admin')) {
                return back()->with('error', 'Super admin foydalanuvchisini ban qilish mumkin emas');
            }

            // Check if user is already banned
            if ($user->isBanned()) {
                return back()->with('error', 'Foydalanuvchi allaqachon ban qilingan');
            }

            $reason = $request->input('reason', 'Admin tomonidan ban qilindi');
            $expiresAt = null;

            if ($request->has('expires_at')) {
                $expiresAt = Carbon::parse($request->expires_at);
            }

            $user->ban($request->user(), $reason, $expiresAt);

            // Revoke all user sessions
            $user->userSessions()->update(['is_active' => false]);
            $user->refreshTokens()->update(['is_revoked' => true]);
            \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $user->id)->delete();

            return back()->with('success', 'Foydalanuvchi muvaffaqiyatli ban qilindi');
        } catch (\Exception $e) {
            return back()->with('error', 'Xatolik: ' . $e->getMessage());
        }
    }

    /**
     * Unban user
     */
    public function unbanUser(Request $request, User $user)
    {
        try {
            // Debug: Check user ban status before unban
            Log::info('Unban attempt for user: ' . $user->id, [
                'is_banned' => $user->is_banned,
                'banned_at' => $user->banned_at,
                'ban_reason' => $user->ban_reason
            ]);

            // Check if user is not banned
            if (!$user->isBanned()) {
                Log::info('User is not banned, cannot unban');
                return back()->with('error', 'Foydalanuvchi ban qilinmagan');
            }

            // Perform unban
            $result = $user->unban();

            Log::info('Unban result: ' . ($result ? 'success' : 'failed'));

            if ($result) {
                // Refresh user data
                $user->refresh();

                Log::info('User after unban: ' . $user->id, [
                    'is_banned' => $user->is_banned,
                    'banned_at' => $user->banned_at,
                    'ban_reason' => $user->ban_reason
                ]);

                return back()->with('success', 'Foydalanuvchi muvaffaqiyatli unban qilindi');
            } else {
                return back()->with('error', 'Unban qilishda xatolik yuz berdi');
            }
        } catch (\Exception $e) {
            Log::error('Unban error: ' . $e->getMessage());
            return back()->with('error', 'Xatolik: ' . $e->getMessage());
        }
    }

    /**
     * Activate user
     */
    public function activateUser(Request $request, User $user)
    {
        try {
            $user->update(['is_active' => true]);
            return back()->with('success', 'Foydalanuvchi muvaffaqiyatli faollashtirildi');
        } catch (\Exception $e) {
            return back()->with('error', 'Xatolik: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate user
     */
    public function deactivateUser(Request $request, User $user)
    {
        try {
            $user->update(['is_active' => false]);
            return back()->with('success', 'Foydalanuvchi muvaffaqiyatli deaktivatsiya qilindi');
        } catch (\Exception $e) {
            return back()->with('error', 'Xatolik: ' . $e->getMessage());
        }
    }
}
