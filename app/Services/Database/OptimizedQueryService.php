<?php

namespace App\Services\Database;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OptimizedQueryService
{
    /**
     * Optimized users query with all necessary relationships
     */
    public function getUsersWithOptimizedRelations(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = 'users_optimized_' . md5(serialize($filters) . $perPage);
        
        return Cache::remember($cacheKey, 300, function () use ($filters, $perPage) {
            $query = User::select([
                'id', 'name', 'email', 'phone', 'is_active', 'is_banned',
                'banned_at', 'ban_reason', 'ban_expires_at', 'created_at'
            ])
            ->with([
                'roles:id,name,guard_name',
                'bannedBy:id,name',
                'permissions:id,name,guard_name'
            ]);

            // Apply filters
            $this->applyUserFilters($query, $filters);

            return $query->orderBy('created_at', 'desc')
                        ->paginate($perPage);
        });
    }

    /**
     * Optimized roles query with permissions count
     */
    public function getRolesWithOptimizedRelations(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = 'roles_optimized_' . md5(serialize($filters) . $perPage);
        
        return Cache::remember($cacheKey, 600, function () use ($filters, $perPage) {
            $query = Role::select(['id', 'name', 'guard_name', 'created_at'])
                ->withCount('permissions')
                ->withCount('users')
                ->with(['permissions:id,name,guard_name']);

            // Apply filters
            $this->applyRoleFilters($query, $filters);

            return $query->orderBy('created_at', 'desc')
                        ->paginate($perPage);
        });
    }

    /**
     * Optimized permissions query with role counts
     */
    public function getPermissionsWithOptimizedRelations(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = 'permissions_optimized_' . md5(serialize($filters) . $perPage);
        
        return Cache::remember($cacheKey, 600, function () use ($filters, $perPage) {
            $query = Permission::select(['id', 'name', 'guard_name', 'created_at'])
                ->withCount('roles')
                ->with(['roles:id,name,guard_name']);

            // Apply filters
            $this->applyPermissionFilters($query, $filters);

            return $query->orderBy('name')
                        ->paginate($perPage);
        });
    }

    /**
     * Get user statistics for dashboard
     */
    public function getUserStatistics(): array
    {
        $cacheKey = 'user_statistics';
        
        return Cache::remember($cacheKey, 300, function () {
            return [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->where('is_banned', false)->count(),
                'banned_users' => User::where('is_banned', true)->count(),
                'inactive_users' => User::where('is_active', false)->where('is_banned', false)->count(),
                'verified_users' => User::whereNotNull('phone_verified_at')->count(),
                'unverified_users' => User::whereNull('phone_verified_at')->count(),
                'users_by_role' => $this->getUsersByRole(),
                'recent_registrations' => User::where('created_at', '>=', now()->subDays(7))->count(),
            ];
        });
    }

    /**
     * Get users by role statistics
     */
    private function getUsersByRole(): array
    {
        return DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name as role_name', DB::raw('COUNT(*) as user_count'))
            ->groupBy('roles.name')
            ->get()
            ->pluck('user_count', 'role_name')
            ->toArray();
    }

    /**
     * Apply user filters to query
     */
    private function applyUserFilters(Builder $query, array $filters): void
    {
        // Filter by ban status
        if (isset($filters['ban_status'])) {
            switch ($filters['ban_status']) {
                case 'banned':
                    $query->where('is_banned', true);
                    break;
                case 'active':
                    $query->where('is_banned', false)->where('is_active', true);
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
            }
        }

        // Filter by role
        if (isset($filters['role']) && $filters['role']) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        // Search by name, phone, or email
        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by verification status
        if (isset($filters['verified'])) {
            if ($filters['verified']) {
                $query->whereNotNull('phone_verified_at');
            } else {
                $query->whereNull('phone_verified_at');
            }
        }

        // Filter by date range
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
    }

    /**
     * Apply role filters to query
     */
    private function applyRoleFilters(Builder $query, array $filters): void
    {
        // Search by name
        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by guard
        if (isset($filters['guard']) && $filters['guard']) {
            $query->where('guard_name', $filters['guard']);
        }
    }

    /**
     * Apply permission filters to query
     */
    private function applyPermissionFilters(Builder $query, array $filters): void
    {
        // Search by name
        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by group (e.g., users.*, properties.*)
        if (isset($filters['group']) && $filters['group']) {
            $group = $filters['group'];
            $query->where('name', 'like', "{$group}%");
        }

        // Filter by guard
        if (isset($filters['guard']) && $filters['guard']) {
            $query->where('guard_name', $filters['guard']);
        }
    }

    /**
     * Clear cache for specific entity
     */
    public function clearCache(string $entity = null): void
    {
        if ($entity) {
            Cache::forget("{$entity}_optimized_*");
        } else {
            Cache::flush();
        }
    }

    /**
     * Get optimized user with all relationships
     */
    public function getUserWithOptimizedRelations(int $userId): ?User
    {
        $cacheKey = "user_optimized_{$userId}";
        
        return Cache::remember($cacheKey, 300, function () use ($userId) {
            return User::select([
                'id', 'name', 'email', 'phone', 'is_active', 'is_banned',
                'banned_at', 'ban_reason', 'ban_expires_at', 'created_at', 'updated_at'
            ])
            ->with([
                'roles:id,name,guard_name',
                'bannedBy:id,name',
                'permissions:id,name,guard_name',
                'userSessions' => function ($query) {
                    $query->select(['id', 'user_id', 'device_name', 'device_type', 'is_active', 'last_activity', 'created_at'])
                          ->where('is_active', true)
                          ->orderBy('last_activity', 'desc');
                }
            ])
            ->find($userId);
        });
    }

    /**
     * Bulk operations with optimized queries
     */
    public function bulkUpdateUsers(array $userIds, array $data): int
    {
        $result = User::whereIn('id', $userIds)->update($data);
        
        // Clear cache after bulk update
        $this->clearCache('users');
        
        return $result;
    }

    /**
     * Get dashboard analytics
     */
    public function getDashboardAnalytics(): array
    {
        $cacheKey = 'dashboard_analytics';
        
        return Cache::remember($cacheKey, 600, function () {
            return [
                'user_statistics' => $this->getUserStatistics(),
                'role_statistics' => $this->getRoleStatistics(),
                'permission_statistics' => $this->getPermissionStatistics(),
                'recent_activity' => $this->getRecentActivity(),
            ];
        });
    }

    /**
     * Get role statistics
     */
    private function getRoleStatistics(): array
    {
        return [
            'total_roles' => Role::count(),
            'roles_with_permissions' => Role::has('permissions')->count(),
            'roles_with_users' => Role::has('users')->count(),
        ];
    }

    /**
     * Get permission statistics
     */
    private function getPermissionStatistics(): array
    {
        return [
            'total_permissions' => Permission::count(),
            'permissions_with_roles' => Permission::has('roles')->count(),
            'permission_groups' => $this->getPermissionGroups(),
        ];
    }

    /**
     * Get permission groups
     */
    private function getPermissionGroups(): array
    {
        return Permission::select(DB::raw('SUBSTRING_INDEX(name, ".", 1) as group_name'))
            ->selectRaw('COUNT(*) as count')
            ->groupBy('group_name')
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count', 'group_name')
            ->toArray();
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(): array
    {
        return [
            'recent_users' => User::select(['id', 'name', 'created_at'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'recent_roles' => Role::select(['id', 'name', 'created_at'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];
    }
} 