import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import LoadingButton from '@/Components/LoadingButton';
import DropdownActions from '@/Components/DropdownActions';
import DropdownActionItem from '@/Components/DropdownActionItem';

export default function RoleManagement({ roles, filters }) {
    const [search, setSearch] = useState(filters.search || '');

    const [loading, setLoading] = useState(false);

    const handleSearch = () => {
        setLoading(true);
        router.get('/super-admin/roles', {
            search
        }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false)
        });
    };

    const handleDeleteRole = (roleId, roleName) => {
        if (confirm(`"${roleName}" rolini o'chirishni xohlaysizmi?`)) {
            router.delete(`/super-admin/roles/${roleId}`);
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Rollar boshqaruvi
                    </h2>
                    <div className="flex space-x-4">
                        <Link
                            href="/super-admin/roles/create"
                            className="inline-flex items-center justify-center h-8 px-3 text-xs font-medium rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 shadow-sm"
                        >
                            Yangi rol
                        </Link>
                        <Link
                            href="/super-admin/permissions"
                            className="inline-flex items-center justify-center h-8 px-3 text-xs font-medium rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 shadow-sm"
                        >
                            Ruxsatlar
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title="Rollar boshqaruvi" />

            <div className="py-1">
                    <div className="mx-auto sm:px-6 lg:px-8">
                    {/* Search */}
                    <div className="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex space-x-4">
                                <div className="flex-1">
                                    <input
                                        type="text"
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        placeholder="Rol nomi bo'yicha qidirish..."
                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    />
                                </div>
                                <LoadingButton
                                    onClick={handleSearch}
                                    loading={loading}
                                    loadingText="Qidirilmoqda..."
                                    variant="white"
                                    size="sm"
                                >
                                    Qidirish
                                </LoadingButton>
                            </div>
                        </div>
                    </div>

                    {/* Roles Table */}
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Rol nomi
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ruxsatlar
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Foydalanuvchilar soni
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Yaratilgan sana
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amallar
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {roles.data.map((role) => (
                                        <tr key={role.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex items-center">
                                                    <div className="flex-shrink-0 h-10 w-10">
                                                        <div className="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                            <span className="text-sm font-medium text-indigo-800">
                                                                {role.name.charAt(0).toUpperCase()}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div className="ml-4">
                                                        <div className="text-sm font-medium text-gray-900">
                                                            {role.name}
                                                        </div>
                                                        <div className="text-sm text-gray-500">
                                                            ID: {role.id}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex flex-wrap gap-1">
                                                    {role.permissions?.slice(0, 3).map((permission) => (
                                                        <span
                                                            key={permission.id}
                                                            className="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"
                                                        >
                                                            {permission.name}
                                                        </span>
                                                    ))}
                                                    {role.permissions?.length > 3 && (
                                                        <span className="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                            +{role.permissions.length - 3} boshqa
                                                        </span>
                                                    )}
                                                    {(!role.permissions || role.permissions.length === 0) && (
                                                        <span className="text-sm text-gray-500">Ruxsat yo'q</span>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {role.users_count || 0}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {new Date(role.created_at).toLocaleDateString('uz-UZ')}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div className="flex justify-center">
                                                    <DropdownActions>
                                                        <Link
                                                            href={`/super-admin/roles/${role.id}`}
                                                            className="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150 ease-in-out"
                                                        >
                                                            Ko'rish
                                                        </Link>
                                                        <Link
                                                            href={`/super-admin/roles/${role.id}/edit`}
                                                            className="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150 ease-in-out"
                                                        >
                                                            Tahrirlash
                                                        </Link>
                                                        {role.name !== 'super-admin' && (
                                                            <DropdownActionItem
                                                                onClick={() => handleDeleteRole(role.id, role.name)}
                                                                variant="danger"
                                                            >
                                                                O'chirish
                                                            </DropdownActionItem>
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
                        {roles.links && (
                            <div className="px-6 py-4 border-t border-gray-200">
                                <div className="flex items-center justify-between">
                                    <div className="text-sm text-gray-700">
                                        {roles.from} dan {roles.to} gacha, jami {roles.total} ta
                                    </div>
                                    <div className="flex space-x-2">
                                        {roles.links.map((link, index) => (
                                            link.url ? (
                                                <Link
                                                    key={index}
                                                    href={link.url}
                                                    className={`px-3 py-2 text-sm rounded-md ${
                                                        link.active
                                                            ? 'bg-indigo-600 text-white'
                                                            : 'bg-white text-gray-700 hover:bg-gray-50'
                                                    }`}
                                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                                />
                                            ) : (
                                                <span
                                                    key={index}
                                                    className={`px-3 py-2 text-sm rounded-md ${
                                                        link.active
                                                            ? 'bg-indigo-600 text-white'
                                                            : 'bg-white text-gray-400'
                                                    }`}
                                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                                />
                                            )
                                        ))}
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
