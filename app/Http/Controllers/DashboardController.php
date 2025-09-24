<?php

namespace App\Http\Controllers;

use App\Services\Auth\AuthServiceInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Display the dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Me API'ni ishlatib to'liq foydalanuvchi ma'lumotlarini olish
        $userData = $this->authService->getAuthenticatedUser($user);

        return Inertia::render('Dashboard', [
            'auth' => [
                'user' => $userData['user']
            ]
        ]);
    }
}
