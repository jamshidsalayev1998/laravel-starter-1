import { usePage } from '@inertiajs/react';

export default function PermissionGate({ permission, role, children, fallback = null }) {
    const { auth } = usePage().props;
    const { user } = auth;

    // Permission check
    const hasPermission = (perm) => {
        if (!user || !user.permissions) return false;
        return user.permissions.some(p => p.name === perm);
    };

    // Role check
    const hasRole = (rol) => {
        if (!user || !user.roles) return false;
        return user.roles.some(r => r.name === rol);
    };

    // Permission yoki role tekshirish
    if (permission && !hasPermission(permission)) {
        return fallback;
    }

    if (role && !hasRole(role)) {
        return fallback;
    }

    return children;
}

// Hook sifatida ishlatish uchun
export function usePermissions() {
    const { auth } = usePage().props;
    const { user } = auth;

    const hasPermission = (permission) => {
        if (!user || !user.permissions) return false;
        return user.permissions.some(p => p.name === permission);
    };

    const hasRole = (role) => {
        if (!user || !user.roles) return false;
        return user.roles.some(r => r.name === role);
    };

    const hasAnyRole = (roles) => {
        if (!user || !user.roles) return false;
        return user.roles.some(r => roles.includes(r.name));
    };

    const hasAnyPermission = (permissions) => {
        if (!user || !user.permissions) return false;
        return user.permissions.some(p => permissions.includes(p.name));
    };

    return {
        hasPermission,
        hasRole,
        hasAnyRole,
        hasAnyPermission,
        user
    };
}
