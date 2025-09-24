import { Link, usePage, router } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';

export default function Sidebar() {
    const { auth } = usePage().props;
    const { user } = auth;
    const [isCollapsed, setIsCollapsed] = useState(() => {
        try {
            return localStorage.getItem('sidebar:collapsed') === '1';
        } catch (e) {
            return false;
        }
    });

    useEffect(() => {
        try {
            localStorage.setItem('sidebar:collapsed', isCollapsed ? '1' : '0');
        } catch (e) {
            // ignore storage errors
        }
    }, [isCollapsed]);

    // Permission check funksiyasi
    const hasPermission = (permission) => {
        if (!user || !user.permissions) return false;
        // permissions endi string massiv: ['users.view', ...]
        if (Array.isArray(user.permissions) && typeof user.permissions[0] === 'string') {
            return user.permissions.includes(permission);
        }
        // orqaga moslik: [{name: 'users.view'}]
        return user.permissions.some(p => p?.name === permission);
    };

    // Bir nechta permissiondan bittasi bo'lsa true
    const hasAnyPermission = (permissions) => {
        if (!permissions || permissions.length === 0) return true;
        return permissions.some(p => hasPermission(p));
    };

    // Navigation funksiyasi
    const handleNavigation = (href) => {
        router.visit(href, {
            preserveState: true,
            preserveScroll: true,
            replace: false, // History'da saqlash uchun
        });
    };

    // Yagona menyu konfiguratsiyasi (permission-ga asoslangan)
    const baseMenu = [
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
            permission: 'users.view',
            children: [
                { name: 'Barcha foydalanuvchilar', href: '/admin/users', permission: 'users.view' },
                { name: 'Ban qilinganlar', href: '/admin/users?status=banned', permission: 'users.view' },
                { name: 'Nofaol foydalanuvchilar', href: '/admin/users?status=inactive', permission: 'users.view' }
            ]
        },
        {
            name: 'Rollar va Ruxsatlar',
            icon: '👑',
            // Parent ko'rinishi roles.view yoki permissions.view bo'lsa ham chiqishi uchun any-perm
            requiredAnyPermissions: ['roles.view', 'permissions.view'],
            children: [
                { name: 'Rollar', href: '/super-admin/roles', permission: 'roles.view' },
                { name: 'Ruxsatlar', href: '/super-admin/permissions', permission: 'permissions.view' }
            ]
        },
        {
            name: 'Qurilmalar',
            href: '/devices',
            icon: '📱',
            permission: null
        },
        {
            name: 'Hisobotlar',
            icon: '📊',
            permission: 'reports.view',
            children: [
                { name: 'Foydalanuvchilar hisoboti', href: '/reports/users', permission: 'reports.users' },
                { name: 'Faollik hisoboti', href: '/reports/activity', permission: 'reports.activity' }
            ]
        },
        {
            name: 'Profil',
            href: '/profile',
            icon: '👤',
            permission: null
        }
    ];

    // Recursive permission filter: item yoki uning bolalaridan kamida bittasi ko'rinishi kerak
    const filterMenuByPermission = (items) => {
        return items
            .map(item => {
                const children = item.children ? filterMenuByPermission(item.children) : null;

                const selfAllowed = item.permission ? hasPermission(item.permission) : true;
                const parentAllowed = item.requiredAnyPermissions ? hasAnyPermission(item.requiredAnyPermissions) : true;

                const hasVisibleChildren = children && children.length > 0;

                const isVisible = (selfAllowed && parentAllowed) || hasVisibleChildren;

                if (!isVisible) return null;

                return { ...item, children };
            })
            .filter(Boolean);
    };

    const filteredMenuItems = filterMenuByPermission(baseMenu);

    return (
        <div className={`bg-gray-800 text-white transition-all duration-300 ${isCollapsed ? 'w-16' : 'w-64'} h-screen overflow-y-auto flex flex-col`}>
            {/* Header */}
            <div className="p-2 border-b border-gray-700">
                <div className="flex items-center justify-between">
                    {!isCollapsed && (
                        <h2 className="text-xl font-bold">StayNow</h2>
                    )}
                    <button
                        onClick={() => setIsCollapsed(!isCollapsed)}
                        className="p-2 rounded-md hover:bg-gray-700"
                    >
                        {isCollapsed ? '→' : '←'}
                    </button>
                </div>
            </div>

            {/* User Info */}
            {!isCollapsed && (
                <div className="p-2 border-b border-gray-700">
                    <div className="flex items-center space-x-3">
                        <div className="h-10 w-10 rounded-full bg-gray-600 flex items-center justify-center">
                            <span className="text-sm font-medium">
                                {user?.name?.charAt(0).toUpperCase()}
                            </span>
                        </div>
                        <div>
                            <div className="text-sm font-medium">{user?.name}</div>
                            <div className="text-xs text-gray-400">
                                {Array.isArray(user?.roles)
                                    ? (typeof user.roles[0] === 'string' ? user.roles.join(', ') : user.roles.map(r => r.name).join(', '))
                                    : 'Guest'}
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Menu Items */}
            <nav className="mt-4 flex-1">
                {filteredMenuItems.map((item, index) => (
                    <MenuItem
                        key={index}
                        item={item}
                        isCollapsed={isCollapsed}
                        hasPermission={hasPermission}
                        onNavigate={handleNavigation}
                        currentUrl={usePage().url}
                    />
                ))}
            </nav>

            {/* Logout */}
            <div className="mt-auto p-2 border-t border-gray-700">
                <Link
                    href="/logout"
                    method="post"
                    className={`w-full flex items-center space-x-3 text-gray-300 hover:text-white hover:bg-gray-700 rounded-md p-2 ${isCollapsed ? 'justify-center' : ''}`}
                >
                    <span>🚪</span>
                    {!isCollapsed && <span>Chiqish</span>}
                </Link>
            </div>
        </div>
    );
}

// Menu Item komponenti
function MenuItem({ item, isCollapsed, hasPermission, onNavigate, currentUrl }) {
    const storageKey = useMemo(() => `sidebar:group:${item.name}`, [item.name]);
    const [isExpanded, setIsExpanded] = useState(() => {
        try {
            const saved = localStorage.getItem(storageKey);
            return saved === '1';
        } catch (e) {
            return false;
        }
    });

    useEffect(() => {
        try {
            localStorage.setItem(storageKey, isExpanded ? '1' : '0');
        } catch (e) {}
    }, [isExpanded, storageKey]);

    // Agar children bo'lsa, dropdown menu
    if (item.children) {
        const visibleChildren = item.children.filter(child => {
            if (!child.permission) return true;
            return hasPermission(child.permission);
        });

        if (visibleChildren.length === 0) return null;

        // Auto-expand if current URL matches any child
        useEffect(() => {
            if (!isCollapsed) {
                const shouldExpand = visibleChildren.some(c => currentUrl && c.href && currentUrl.startsWith(c.href));
                if (shouldExpand) setIsExpanded(true);
            }
        }, [currentUrl]);

        return (
            <div className="mb-1">
                <button
                    onClick={() => setIsExpanded(prev => !prev)}
                    className={`w-full flex items-center space-x-3 text-gray-300 hover:text-white hover:bg-gray-700 rounded-md p-1 ${isCollapsed ? 'justify-center' : ''}`}
                    aria-expanded={isExpanded}
                    aria-controls={`group-${item.name}`}
                    role="button"
                    onKeyDown={(e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            setIsExpanded(prev => !prev);
                        }
                    }}
                >
                    <span>{item.icon}</span>
                    {!isCollapsed && (
                        <>
                            <span className="flex-1 text-left">{item.name}</span>
                            <span className={isExpanded ? 'transform rotate-180' : ''}>▼</span>
                        </>
                    )}
                </button>

                {isExpanded && !isCollapsed && (
                    <div id={`group-${item.name}`} className="ml-4 space-y-1">
                        {visibleChildren.map((child, childIndex) => (
                            <button
                                key={childIndex}
                                onClick={() => onNavigate(child.href)}
                                className={`block w-full text-left text-sm rounded-md p-1 ${currentUrl && child.href && currentUrl.startsWith(child.href) ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-700'}`}
                            >
                                {child.name}
                            </button>
                        ))}
                    </div>
                )}
            </div>
        );
    }

    // Oddiy menu item
    return (
        <button
            onClick={() => onNavigate(item.href)}
            className={`w-full flex items-center space-x-3 text-gray-300 hover:text-white hover:bg-gray-700 rounded-md p-1 mb-1 ${isCollapsed ? 'justify-center' : ''}`}
        >
            <span>{item.icon}</span>
            {!isCollapsed && <span>{item.name}</span>}
        </button>
    );
}
