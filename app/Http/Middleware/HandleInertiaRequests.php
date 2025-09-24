<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => fn () => (
                function () use ($request) {
                    $user = $request->user();
                    if (!$user) {
                        return ['user' => null];
                    }

                    // Spatie permission: roles as names, permissions as names
                    $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->toArray() : [];
                    $permissions = method_exists($user, 'getAllPermissions')
                        ? $user->getAllPermissions()->pluck('name')->toArray()
                        : [];

                    return [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'roles' => $roles,
                            'permissions' => $permissions,
                        ],
                    ];
                }
            )(),
        ];
    }
}
