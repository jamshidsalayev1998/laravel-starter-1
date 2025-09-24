import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function UserDetails({ user }) {
    const handleBanUser = () => {
        const reason = prompt('Ban sababi:', 'Admin tomonidan ban qilindi');
        if (reason !== null) {
            router.post(`/admin/users/${user.id}/ban`, {
                reason,
                expires_at: null
            });
        }
    };

    const handleUnbanUser = () => {
        if (confirm('Bu foydalanuvchini unban qilishni xohlaysizmi?')) {
            router.post(`/admin/users/${user.id}/unban`);
        }
    };

    const handleActivateUser = () => {
        if (confirm('Bu foydalanuvchini faollashtirishni xohlaysizmi?')) {
            router.post(`/admin/users/${user.id}/activate`);
        }
    };

    const handleDeactivateUser = () => {
        if (confirm('Bu foydalanuvchini deaktivatsiya qilishni xohlaysizmi?')) {
            router.post(`/admin/users/${user.id}/deactivate`);
        }
    };

    const getStatusBadge = () => {
        if (user.is_banned) {
            return (
                <span className="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                    Ban qilingan
                </span>
            );
        }
        if (user.is_active) {
            return (
                <span className="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                    Faol
                </span>
            );
        }
        return (
            <span className="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                Nofaol
            </span>
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Foydalanuvchi ma'lumotlari
                    </h2>
                    <Link
                        href="/admin/users"
                        className="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
                    >
                        Orqaga qaytish
                    </Link>
                </div>
            }
        >
            <Head title={`${user.name} - Foydalanuvchi ma'lumotlari`} />

            <div className="py-1">
                <div className="mx-auto  sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* User Info */}
                        <div className="lg:col-span-2">
                            <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <div className="flex items-center mb-6">
                                        <div className="flex-shrink-0 h-16 w-16">
                                            <div className="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span className="text-2xl font-medium text-gray-700">
                                                    {user.name.charAt(0).toUpperCase()}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="ml-6">
                                            <h3 className="text-2xl font-bold text-gray-900">{user.name}</h3>
                                            <p className="text-gray-600">ID: {user.id}</p>
                                            <div className="mt-2">
                                                {getStatusBadge()}
                                            </div>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <h4 className="text-lg font-medium text-gray-900 mb-4">Asosiy ma'lumotlar</h4>
                                            <dl className="space-y-3">
                                                <div>
                                                    <dt className="text-sm font-medium text-gray-500">Telefon</dt>
                                                    <dd className="text-sm text-gray-900">{user.phone}</dd>
                                                </div>
                                                <div>
                                                    <dt className="text-sm font-medium text-gray-500">Email</dt>
                                                    <dd className="text-sm text-gray-900">{user.email || 'Kiritilmagan'}</dd>
                                                </div>
                                                <div>
                                                    <dt className="text-sm font-medium text-gray-500">Ro'yxatdan o'tgan sana</dt>
                                                    <dd className="text-sm text-gray-900">
                                                        {new Date(user.created_at).toLocaleDateString('uz-UZ')}
                                                    </dd>
                                                </div>
                                                <div>
                                                    <dt className="text-sm font-medium text-gray-500">Oxirgi yangilanish</dt>
                                                    <dd className="text-sm text-gray-900">
                                                        {new Date(user.updated_at).toLocaleDateString('uz-UZ')}
                                                    </dd>
                                                </div>
                                            </dl>
                                        </div>

                                        <div>
                                            <h4 className="text-lg font-medium text-gray-900 mb-4">Ban ma'lumotlari</h4>
                                            {user.is_banned ? (
                                                <dl className="space-y-3">
                                                    <div>
                                                        <dt className="text-sm font-medium text-gray-500">Ban qilingan sana</dt>
                                                        <dd className="text-sm text-gray-900">
                                                            {new Date(user.banned_at).toLocaleDateString('uz-UZ')}
                                                        </dd>
                                                    </div>
                                                    <div>
                                                        <dt className="text-sm font-medium text-gray-500">Ban sababi</dt>
                                                        <dd className="text-sm text-gray-900">{user.ban_reason || 'Kiritilmagan'}</dd>
                                                    </div>
                                                    <div>
                                                        <dt className="text-sm font-medium text-gray-500">Ban qilgan admin</dt>
                                                        <dd className="text-sm text-gray-900">
                                                            {user.banned_by ? user.banned_by.name : 'Noma\'lum'}
                                                        </dd>
                                                    </div>
                                                    {user.ban_expires_at && (
                                                        <div>
                                                            <dt className="text-sm font-medium text-gray-500">Ban tugash sanasi</dt>
                                                            <dd className="text-sm text-gray-900">
                                                                {new Date(user.ban_expires_at).toLocaleDateString('uz-UZ')}
                                                            </dd>
                                                        </div>
                                                    )}
                                                </dl>
                                            ) : (
                                                <p className="text-sm text-gray-500">Bu foydalanuvchi ban qilinmagan</p>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Actions & Roles */}
                        <div className="space-y-6">
                            {/* Actions */}
                            <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <h4 className="text-lg font-medium text-gray-900 mb-4">Amallar</h4>
                                    <div className="space-y-3">
                                        {!user.roles?.some(role => role.name === 'super-admin') && (
                                            <>
                                                {user.is_banned ? (
                                                    <button
                                                        onClick={handleUnbanUser}
                                                        className="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                                                    >
                                                        Unban qilish
                                                    </button>
                                                ) : (
                                                    <button
                                                        onClick={handleBanUser}
                                                        className="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                                                    >
                                                        Ban qilish
                                                    </button>
                                                )}
                                                {user.is_active ? (
                                                    <button
                                                        onClick={handleDeactivateUser}
                                                        className="w-full px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700"
                                                    >
                                                        Deaktivatsiya qilish
                                                    </button>
                                                ) : (
                                                    <button
                                                        onClick={handleActivateUser}
                                                        className="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                                                    >
                                                        Faollashtirish
                                                    </button>
                                                )}
                                            </>
                                        )}
                                        <Link
                                            href={`/super-admin/users/${user.id}/access`}
                                            className="w-full block text-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                        >
                                            Rol va ruxsatlarni boshqarish
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            {/* Roles */}
                            <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <h4 className="text-lg font-medium text-gray-900 mb-4">Rollar</h4>
                                    <div className="space-y-2">
                                        {user.roles?.length > 0 ? (
                                            user.roles.map((role) => (
                                                <span
                                                    key={role.id}
                                                    className="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800"
                                                >
                                                    {role.name}
                                                </span>
                                            ))
                                        ) : (
                                            <p className="text-sm text-gray-500">Hech qanday rol berilmagan</p>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {/* Permissions */}
                            <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <h4 className="text-lg font-medium text-gray-900 mb-4">Ruxsatlar</h4>
                                    <div className="space-y-2">
                                        {user.permissions?.length > 0 ? (
                                            user.permissions.map((permission) => (
                                                <span
                                                    key={permission.id}
                                                    className="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"
                                                >
                                                    {permission.name}
                                                </span>
                                            ))
                                        ) : (
                                            <p className="text-sm text-gray-500">Hech qanday ruxsat berilmagan</p>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
