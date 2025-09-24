<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\Database\OptimizedQueryService;
use App\Models\User;
use App\Http\Requests\Api\SuperAdmin\BanUserRequest;
use App\Http\Requests\Api\SuperAdmin\UnbanUserRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserManagementController extends Controller
{
    protected OptimizedQueryService $queryService;

    public function __construct(OptimizedQueryService $queryService)
    {
        $this->queryService = $queryService;
    }

    /**
     * Get all users with pagination (optimized)
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['ban_status', 'role', 'search', 'verified', 'date_from', 'date_to']);
            $perPage = $request->get('per_page', 15);
            
            $users = $this->queryService->getUsersWithOptimizedRelations($filters, $perPage);

            return $this->apiPaginated($users, 'Foydalanuvchilar muvaffaqiyatli olindi');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Foydalanuvchilarni olishda xatolik', 'user_management');
        }
    }

    /**
     * Get specific user details (optimized)
     */
    public function show(User $user)
    {
        try {
            $user = $this->queryService->getUserWithOptimizedRelations($user->id);
            
            if (!$user) {
                return $this->apiNotFound('Foydalanuvchi topilmadi');
            }

            return $this->apiSuccess('Foydalanuvchi ma\'lumotlari olindi', [
                'user' => $user,
                'ban_info' => $user->getBanInfo(),
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Foydalanuvchi ma\'lumotlarini olishda xatolik', 'user_management');
        }
    }

    /**
     * Ban user (permanent or temporary)
     */
    public function banUser(BanUserRequest $request, User $user)
    {
        try {
            // Check if trying to ban super admin
            if ($user->hasRole('super-admin')) {
                return $this->apiForbidden('Super admin foydalanuvchisini ban qilish mumkin emas');
            }

            // Check if user is already banned
            if ($user->isBanned()) {
                return $this->apiError('Foydalanuvchi allaqachon ban qilingan', null, 400);
            }

            $expiresAt = null;
            if ($request->has('expires_at')) {
                $expiresAt = Carbon::parse($request->expires_at);
            }

            $user->ban($request->user(), $request->reason, $expiresAt);

            // Revoke all user sessions
            $user->userSessions()->update(['is_active' => false]);
            $user->refreshTokens()->update(['is_revoked' => true]);
            \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $user->id)->delete();

            // Clear cache
            $this->queryService->clearCache('users');

            return $this->apiSuccess('Foydalanuvchi muvaffaqiyatli ban qilindi', [
                'user' => $user->fresh(['roles', 'bannedBy']),
                'ban_info' => $user->getBanInfo(),
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Foydalanuvchini ban qilishda xatolik', 'user_management');
        }
    }

    /**
     * Unban user
     */
    public function unbanUser(UnbanUserRequest $request, User $user)
    {
        try {
            // Check if user is not banned
            if (!$user->isBanned()) {
                return $this->apiError('Foydalanuvchi ban qilinmagan', null, 400);
            }

            $user->unban();

            // Clear cache
            $this->queryService->clearCache('users');

            return $this->apiSuccess('Foydalanuvchi muvaffaqiyatli unban qilindi', [
                'user' => $user->fresh(['roles']),
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Foydalanuvchini unban qilishda xatolik', 'user_management');
        }
    }

    /**
     * Get banned users (optimized)
     */
    public function getBannedUsers(Request $request)
    {
        try {
            $filters = array_merge($request->only(['ban_type', 'search']), ['ban_status' => 'banned']);
            $perPage = $request->get('per_page', 15);
            
            $users = $this->queryService->getUsersWithOptimizedRelations($filters, $perPage);

            return $this->apiPaginated($users, 'Ban qilingan foydalanuvchilar olindi');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Ban qilingan foydalanuvchilarni olishda xatolik', 'user_management');
        }
    }

    /**
     * Activate user account
     */
    public function activateUser(Request $request, User $user)
    {
        try {
            $user->update(['is_active' => true]);
            
            // Clear cache
            $this->queryService->clearCache('users');

            return $this->apiSuccess('Foydalanuvchi muvaffaqiyatli faollashtirildi', [
                'user' => $user->fresh(['roles'])
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Foydalanuvchini faollashtirishda xatolik', 'user_management');
        }
    }

    /**
     * Deactivate user account
     */
    public function deactivateUser(Request $request, User $user)
    {
        try {
            // Check if trying to deactivate super admin
            if ($user->hasRole('super-admin')) {
                return $this->apiForbidden('Super admin foydalanuvchisini deaktivatsiya qilish mumkin emas');
            }

            $user->update(['is_active' => false]);

            // Revoke all user sessions
            $user->userSessions()->update(['is_active' => false]);
            $user->refreshTokens()->update(['is_revoked' => true]);
            \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $user->id)->delete();

            // Clear cache
            $this->queryService->clearCache('users');

            return $this->apiSuccess('Foydalanuvchi muvaffaqiyatli deaktivatsiya qilindi', [
                'user' => $user->fresh(['roles'])
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Foydalanuvchini deaktivatsiya qilishda xatolik', 'user_management');
        }
    }

    /**
     * Get user statistics for dashboard
     */
    public function getStatistics()
    {
        try {
            $statistics = $this->queryService->getUserStatistics();
            
            return $this->apiSuccess('Statistika ma\'lumotlari olindi', $statistics);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Statistika ma\'lumotlarini olishda xatolik', 'user_management');
        }
    }

    /**
     * Bulk operations on users
     */
    public function bulkAction(Request $request)
    {
        try {
            $request->validate([
                'action' => 'required|in:activate,deactivate,ban,unban',
                'user_ids' => 'required|array|min:1',
                'user_ids.*' => 'integer|exists:users,id'
            ]);

            $action = $request->action;
            $userIds = $request->user_ids;
            $data = [];

            switch ($action) {
                case 'activate':
                    $data = ['is_active' => true];
                    break;
                case 'deactivate':
                    $data = ['is_active' => false];
                    break;
                case 'ban':
                    $data = [
                        'is_banned' => true,
                        'banned_at' => now(),
                        'banned_by' => auth()->id(),
                        'ban_reason' => $request->reason ?? 'Bulk ban',
                    ];
                    break;
                case 'unban':
                    $data = [
                        'is_banned' => false,
                        'banned_at' => null,
                        'banned_by' => null,
                        'ban_reason' => null,
                        'ban_expires_at' => null,
                    ];
                    break;
            }

            $updatedCount = $this->queryService->bulkUpdateUsers($userIds, $data);

            return $this->apiSuccess("{$updatedCount} ta foydalanuvchi muvaffaqiyatli {$action} qilindi");
        } catch (\Exception $e) {
            return $this->handleException($e, 'Bulk operatsiyada xatolik', 'user_management');
        }
    }
}
