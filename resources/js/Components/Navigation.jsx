import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function Navigation() {
    const { auth } = usePage().props;
    const { user } = auth;
    const [isOpen, setIsOpen] = useState(false);

    // Permission check funksiyasi
    const hasPermission = (permission) => {
        if (!user || !user.permissions) return false;
        return user.permissions.some(p => p.name === permission);
    };

    // Role check funksiyasi
    const hasRole = (role) => {
        if (!user || !user.roles) return false;
        return user.roles.some(r => r.name === role);
    };

    // Super Admin uchun menu
    const superAdminMenu = [
        {
            name: 'Dashboard',
            href: '/dashboard',
            icon: '🏠',
            permission: null
        },
        {
            name: 'Foydalanuvchilar',
            href: '/admin/users',
            icon: '👥',
            permission: 'users.view'
        },
        {
            name: 'Rollar',
            href: '/super-admin/roles',
            icon: '👑',
            permission: 'roles.view'
        },
        {
            name: 'Ruxsatlar',
            href: '/super-admin/permissions',
            icon: '🔐',
            permission: 'permissions.view'
        },
        {
            name: 'Qurilmalar',
            href: '/devices',
            icon: '📱',
            permission: null
        }
    ];

    // Admin uchun menu
    const adminMenu = [
        {
            name: 'Dashboard',
            href: '/dashboard',
            icon: '🏠',
            permission: null
        },
        {
            name: 'Foydalanuvchilar',
            href: '/admin/users',
            icon: '👥',
            permission: 'users.view'
        },
        {
            name: 'Qurilmalar',
            href: '/devices',
            icon: '📱',
            permission: null
        }
    ];

    // Guest uchun menu
    const guestMenu = [
        {
            name: 'Dashboard',
            href: '/dashboard',
            icon: '🏠',
            permission: null
        },
        {
            name: 'Profil',
            href: '/profile',
            icon: '👤',
            permission: null
        }
    ];

    // Role'ga qarab menu tanlash
    const getMenuItems = () => {
        if (hasRole('super-admin')) {
            return superAdminMenu;
        } else if (hasRole('admin')) {
            return adminMenu;
        } else {
            return guestMenu;
        }
    };

    // Permission'ga qarab menu item'ni filterlash
    const filteredMenuItems = getMenuItems().filter(item => {
        if (!item.permission) return true;
        return hasPermission(item.permission);
    });

    return (
        <nav className="bg-white shadow-lg">
            <div className="max-w-7xl mx-auto px-4">
                <div className="flex justify-between h-16">
                    <div className="flex">
                        {/* Logo */}
                        <div className="flex-shrink-0 flex items-center">
                            <Link href="/dashboard" className="text-xl font-bold text-gray-800">
                                StayNow
                            </Link>
                        </div>

                        {/* Desktop Menu */}
                        <div className="hidden md:ml-6 md:flex md:space-x-8">
                            {filteredMenuItems.map((item) => (
                                <Link
                                    key={item.name}
                                    href={item.href}
                                    className="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent"
                                >
                                    <span className="mr-2">{item.icon}</span>
                                    {item.name}
                                </Link>
                            ))}
                        </div>
                    </div>

                    {/* User Menu */}
                    <div className="flex items-center">
                        <div className="ml-3 relative">
                            <div className="flex items-center space-x-4">
                                {/* User Info */}
                                <div className="hidden md:block">
                                    <div className="text-sm font-medium text-gray-700">
                                        {user?.name}
                                    </div>
                                    <div className="text-xs text-gray-500">
                                        {user?.roles?.map(role => role.name).join(', ') || 'Guest'}
                                    </div>
                                </div>

                                {/* User Avatar */}
                                <div className="flex-shrink-0">
                                    <div className="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span className="text-sm font-medium text-gray-700">
                                            {user?.name?.charAt(0).toUpperCase()}
                                        </span>
                                    </div>
                                </div>

                                {/* Logout Button */}
                                <Link
                                    href="/logout"
                                    method="post"
                                    className="text-sm text-gray-500 hover:text-gray-700"
                                >
                                    Chiqish
                                </Link>
                            </div>
                        </div>

                        {/* Mobile menu button */}
                        <div className="md:hidden">
                            <button
                                onClick={() => setIsOpen(!isOpen)}
                                className="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
                            >
                                <span className="sr-only">Open main menu</span>
                                {isOpen ? (
                                    <svg className="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                ) : (
                                    <svg className="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                )}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {/* Mobile Menu */}
            {isOpen && (
                <div className="md:hidden">
                    <div className="pt-2 pb-3 space-y-1">
                        {filteredMenuItems.map((item) => (
                            <Link
                                key={item.name}
                                href={item.href}
                                className="block pl-3 pr-4 py-2 text-base font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-50"
                                onClick={() => setIsOpen(false)}
                            >
                                <span className="mr-2">{item.icon}</span>
                                {item.name}
                            </Link>
                        ))}
                    </div>
                </div>
            )}
        </nav>
    );
}
