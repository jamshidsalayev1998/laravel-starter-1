<?php

namespace App\Http\Controllers;

use App\Services\Auth\AuthServiceInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeviceController extends Controller
{
    protected AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Qurilmalar sahifasini ko'rsatish
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // To'liq foydalanuvchi ma'lumotlarini olish
        $userData = $this->authService->getAuthenticatedUser($user);

        return Inertia::render('Devices/Index', [
            'title' => 'Qurilmalar',
            'auth' => [
                'user' => $userData['user']
            ]
        ]);
    }
}
