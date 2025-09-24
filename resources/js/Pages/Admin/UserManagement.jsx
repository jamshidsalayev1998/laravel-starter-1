import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import LoadingCard from '@/Components/LoadingCard';
import LoadingButton from '@/Components/LoadingButton';
import DropdownActions from '@/Components/DropdownActions';
import DropdownActionItem from '@/Components/DropdownActionItem';
import { useToast } from '@/Components/ToastContainer';

export default function UserManagement({ users, roles, filters }) {
    const [search, setSearch] = useState(filters.search || '');
    const [roleFilter, setRoleFilter] = useState(filters.role || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');
    const [loading, setLoading] = useState(false);
    const [actionLoading, setActionLoading] = useState({});

    const { showSuccess, showError } = useToast();

    const handleSearch = () => {
        setLoading(true);
        router.get('/admin/users', {
            search,
            role: roleFilter,
            status: statusFilter
        }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false)
        });
    };

    const handleAction = async (action, userId, data = {}) => {
        setActionLoading(prev => ({ ...prev, [userId]: true }));

        try {
            await new Promise((resolve, reject) => {
                router.post(`/admin/users/${userId}/${action}`, data, {
                    onSuccess: () => {
                        showSuccess(`Foydalanuvchi ${action} qilindi`);
                        resolve();
                    },
                    onError: (errors) => {
                        showError(errors.message || 'Xatolik yuz berdi');
                        reject(errors);
                    },
                    onFinish: () => {
                        setActionLoading(prev => ({ ...prev, [userId]: false }));
                    }
                });
            });
        } catch (error) {
            console.error('Action error:', error);
        }
    };

    const handleBanUser = (userId) => {
        if (confirm('Bu foydalanuvchini ban qilishni xohlaysizmi?')) {
            handleAction('ban', userId, {
                reason: 'Admin tomonidan ban qilindi',
                expires_at: null
            });
        }
    };

    const handleUnbanUser = (userId) => {
        if (confirm('Bu foydalanuvchini unban qilishni xohlaysizmi?')) {
            handleAction('unban', userId);
        }
    };

    const handleActivateUser = (userId) => {
        if (confirm('Bu foydalanuvchini faollashtirishni xohlaysizmi?')) {
            handleAction('activate', userId);
        }
    };

    const handleDeactivateUser = (userId) => {
        if (confirm('Bu foydalanuvchini deaktivatsiya qilishni xohlaysizmi?')) {
            handleAction('deactivate', userId);
        }
    };

    const getStatusBadge = (user) => {
        if (user.is_banned) {
            return (
                <span className="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                    Ban qilingan
                </span>
            );
        } else if (!user.is_active) {
            return (
                <span className="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                    Nofaol
                </span>
            );
        } else {
            return (
                <span className="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                    Faol
                </span>
            );
        }
    };

    if (loading) {
        return (
            <AuthenticatedLayout
                header={
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Foydalanuvchilar boshqaruvi
                    </h2>
                }
            >
                <Head title="Foydalanuvchilar boshqaruvi" />
                <div className="py-12">
                    <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                        <LoadingCard message="Foydalanuvchilar yuklanmoqda..." />
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Foydalanuvchilar boshqaruvi
                </h2>
            }
        >
            <Head title="Foydalanuvchilar boshqaruvi" />

            <div className="py-1">
                <div className="mx-auto  sm:px-6 ">
                    {/* Filters */}
                    <div className="mb-6 bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Qidirish
                                    </label>
                                    <input
                                        type="text"
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        placeholder="Ism, telefon yoki email bo'yicha qidirish"
                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Rol
                                    </label>
                                    <select
                                        value={roleFilter}
                                        onChange={(e) => setRoleFilter(e.target.value)}
                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    >
                                        <option value="">Barcha rollar</option>
                                        {roles.map((role) => (
                                            <option key={role.id} value={role.name}>
                                                {role.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Holat
                                    </label>
                                    <select
                                        value={statusFilter}
                                        onChange={(e) => setStatusFilter(e.target.value)}
                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    >
                                        <option value="">Barcha holatlar</option>
                                        <option value="active">Faol</option>
                                        <option value="inactive">Nofaol</option>
                                        <option value="banned">Ban qilingan</option>
                                    </select>
                                </div>
                                <div className="flex items-end">
                                    <LoadingButton
                                        onClick={handleSearch}
                                        loading={loading}
                                        loadingText="Qidirilmoqda..."
                                        variant="white"
                                        size="sm"
                                        className="w-full"
                                    >
                                        Qidirish
                                    </LoadingButton>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Users Table */}
                    <div className="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Foydalanuvchi
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Telefon
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Rollar
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Holat
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amallar
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {users.data?.map((user) => (
                                        <tr key={user.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex items-center">
                                                    <div className="flex-shrink-0 h-10 w-10">
                                                        <div className="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                            <span className="text-sm font-medium text-gray-700">
                                                                {user.name?.charAt(0).toUpperCase() || 'U'}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div className="ml-4">
                                                        <div className="text-sm font-medium text-gray-900">
                                                            {user.name || 'Noma\'lum'}
                                                        </div>
                                                        <div className="text-sm text-gray-500">
                                                            ID: {user.id || 'N/A'}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {user.phone || '-'}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {user.email || '-'}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex flex-wrap gap-1">
                                                    {user.roles?.map((role) => (
                                                        <span
                                                            key={role.id}
                                                            className="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800"
                                                        >
                                                            {role.name}
                                                        </span>
                                                    )) || (
                                                        <span className="text-sm text-gray-500">Rol yo'q</span>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                {getStatusBadge(user)}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div className="flex justify-center">
                                                    <DropdownActions>
                                                        {user.id && (
                                                            <Link
                                                                href={`/admin/users/${user.id}`}
                                                                className="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150 ease-in-out"
                                                            >
                                                                Ko'rish
                                                            </Link>
                                                        )}
                                                        {!user.roles?.some(role => role.name === 'super-admin') && user.id && (
                                                            <>
                                                                {user.is_banned ? (
                                                                    <DropdownActionItem
                                                                        onClick={() => handleUnbanUser(user.id)}
                                                                        loading={actionLoading[user.id]}
                                                                        variant="success"
                                                                    >
                                                                        Unban
                                                                    </DropdownActionItem>
                                                                ) : (
                                                                    <DropdownActionItem
                                                                        onClick={() => handleBanUser(user.id)}
                                                                        loading={actionLoading[user.id]}
                                                                        variant="danger"
                                                                    >
                                                                        Ban
                                                                    </DropdownActionItem>
                                                                )}
                                                                {user.is_active ? (
                                                                    <DropdownActionItem
                                                                        onClick={() => handleDeactivateUser(user.id)}
                                                                        loading={actionLoading[user.id]}
                                                                        variant="warning"
                                                                    >
                                                                        Deaktivatsiya
                                                                    </DropdownActionItem>
                                                                ) : (
                                                                    <DropdownActionItem
                                                                        onClick={() => handleActivateUser(user.id)}
                                                                        loading={actionLoading[user.id]}
                                                                        variant="success"
                                                                    >
                                                                        Faollashtirish
                                                                    </DropdownActionItem>
                                                                )}
                                                            </>
                                                        )}
                                                    </DropdownActions>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {users.links && (
                            <div className="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                                <div className="flex-1 flex justify-between sm:hidden">
                                    {users.prev_page_url && (
                                        <Link
                                            href={users.prev_page_url}
                                            className="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                        >
                                            Oldingi
                                        </Link>
                                    )}
                                    {users.next_page_url && (
                                        <Link
                                            href={users.next_page_url}
                                            className="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                        >
                                            Keyingi
                                        </Link>
                                    )}
                                </div>
                                <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p className="text-sm text-gray-700">
                                            <span className="font-medium">{users.from}</span>
                                            {' '}dan{' '}
                                            <span className="font-medium">{users.to}</span>
                                            {' '}gacha, jami{' '}
                                            <span className="font-medium">{users.total}</span>
                                            {' '}natija
                                        </p>
                                    </div>
                                    <div>
                                        <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                            {users.links.map((link, index) => (
                                                <Link
                                                    key={index}
                                                    href={link.url || '#'}
                                                    className={`relative inline-flex items-center px-4 py-2 border text-sm font-medium ${
                                                        link.active
                                                            ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                                                    } ${
                                                        index === 0 ? 'rounded-l-md' : ''
                                                    } ${
                                                        index === users.links.length - 1 ? 'rounded-r-md' : ''
                                                    }`}
                                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                                />
                                            ))}
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
