import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import Sidebar from '@/Components/Sidebar';
import Navigation from '@/Components/Navigation';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function AuthenticatedLayout({ header, children }) {
    const user = usePage().props.auth.user;
    const [sidebarOpen, setSidebarOpen] = useState(false);

    // Role check funksiyasi
    const hasRole = (role) => {
        if (!user || !user.roles) return false;
        const roles = user.roles;
        if (Array.isArray(roles) && roles.length > 0) {
            if (typeof roles[0] === 'string') {
                return roles.includes(role);
            }
            return roles.some(r => r?.name === role);
        }
        return false;
    };

    // Admin yoki Super Admin uchun sidebar layout
    if (hasRole('admin') || hasRole('super-admin')) {
        return (
            <div className="h-screen overflow-hidden bg-gray-100 flex">
                {/* Sidebar */}
                <Sidebar />

                {/* Main Content */}
                <div className="flex-1 flex flex-col h-screen overflow-hidden">
                    {/* Top Navigation */}
                    {/* <Navigation /> */}

                    {/* Header */}
                    {header && (
                        <header className="bg-white shadow">
                            <div className="mx-auto  px-3 py-3 sm:px-6 lg:px-8">
                                {header}
                            </div>
                        </header>
                    )}

                    {/* Main Content */}
                    <main className="flex-1 overflow-y-auto">{children}</main>
                </div>
            </div>
        );
    }

    // Guest uchun oddiy layout
    return (
        <div className="min-h-screen bg-gray-100">
            {/* Top Navigation */}
            <Navigation />

            {/* Header */}
            {header && (
                <header className="bg-white shadow">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}

            {/* Main Content */}
            <main>{children}</main>
        </div>
    );
}
