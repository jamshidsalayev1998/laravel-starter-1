import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import LoadingButton from '@/Components/LoadingButton';
import PrimaryButton from '@/Components/PrimaryButton';

export default function RoleDetails({ role, allPermissions }) {
    const [selectedPermissions, setSelectedPermissions] = useState(
        role.permissions?.map(p => p.id) || []
    );

    const handlePermissionToggle = (permissionId) => {
        setSelectedPermissions(prev => {
            if (prev.includes(permissionId)) {
                return prev.filter(id => id !== permissionId);
            } else {
                return [...prev, permissionId];
            }
        });
    };

    const [saving, setSaving] = useState(false);
    const handleSavePermissions = () => {
        setSaving(true);
        router.post(`/super-admin/roles/${role.id}/permissions`, {
            permissions: selectedPermissions
        }, {
            onFinish: () => setSaving(false)
        });
    };

    const handleDeleteRole = () => {
        if (confirm(`"${role.name}" rolini o'chirishni xohlaysizmi?`)) {
            router.delete(`/super-admin/roles/${role.id}`);
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        {role.name} - Rol ma'lumotlari
                    </h2>
                    <div className="flex space-x-2">
                        <Link
                            href="/super-admin/roles"
                            className="inline-flex items-center justify-center h-8 px-3 text-xs font-medium rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 shadow-sm"
                        >
                            Orqaga qaytish
                        </Link>
                        <Link
                            href={`/super-admin/roles/${role.id}/edit`}
                            className="inline-flex items-center justify-center h-8 px-3 text-xs font-medium rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 shadow-sm"
                        >
                            Tahrirlash
                        </Link>
                        {role.name !== 'super-admin' && (
                            <PrimaryButton variant="white" size="sm" onClick={handleDeleteRole}>
                                O'chirish
                            </PrimaryButton>
                        )}
                    </div>
                </div>
            }
        >
            <Head title={`${role.name} - Rol ma'lumotlari`} />

            <div className="py-12">
                <div className="mx-auto sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Role Info */}
                        <div className="lg:col-span-1">
                            <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <div className="flex items-center mb-6">
                                        <div className="flex-shrink-0 h-16 w-16">
                                            <div className="h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center">
                                                <span className="text-2xl font-medium text-indigo-800">
                                                    {role.name.charAt(0).toUpperCase()}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="ml-6">
                                            <h3 className="text-2xl font-bold text-gray-900">{role.name}</h3>
                                            <p className="text-gray-600">ID: {role.id}</p>
                                        </div>
                                    </div>

                                    <div className="space-y-4">
                                        <div>
                                            <h4 className="text-lg font-medium text-gray-900 mb-2">Statistika</h4>
                                            <dl className="space-y-2">
                                                <div className="flex justify-between">
                                                    <dt className="text-sm text-gray-500">Ruxsatlar soni:</dt>
                                                    <dd className="text-sm font-medium text-gray-900">
                                                        {role.permissions?.length || 0}
                                                    </dd>
                                                </div>
                                                <div className="flex justify-between">
                                                    <dt className="text-sm text-gray-500">Foydalanuvchilar soni:</dt>
                                                    <dd className="text-sm font-medium text-gray-900">
                                                        {role.users?.length || 0}
                                                    </dd>
                                                </div>
                                                <div className="flex justify-between">
                                                    <dt className="text-sm text-gray-500">Yaratilgan sana:</dt>
                                                    <dd className="text-sm font-medium text-gray-900">
                                                        {new Date(role.created_at).toLocaleDateString('uz-UZ')}
                                                    </dd>
                                                </div>
                                            </dl>
                                        </div>

                                        {role.users && role.users.length > 0 && (
                                            <div>
                                                <h4 className="text-lg font-medium text-gray-900 mb-2">Foydalanuvchilar</h4>
                                                <div className="space-y-2">
                                                    {role.users.slice(0, 5).map((user) => (
                                                        <div key={user.id} className="flex items-center">
                                                            <div className="flex-shrink-0 h-8 w-8">
                                                                <div className="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                                    <span className="text-xs font-medium text-gray-700">
                                                                        {user.name.charAt(0).toUpperCase()}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div className="ml-3">
                                                                <p className="text-sm font-medium text-gray-900">
                                                                    {user.name}
                                                                </p>
                                                                <p className="text-xs text-gray-500">
                                                                    {user.phone}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    ))}
                                                    {role.users.length > 5 && (
                                                        <p className="text-xs text-gray-500">
                                                            +{role.users.length - 5} boshqa foydalanuvchi
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Permissions Management */}
                        <div className="lg:col-span-2">
                            <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <div className="flex items-center justify-between mb-6">
                                        <h4 className="text-lg font-medium text-gray-900">
                                            Ruxsatlar boshqaruvi
                                        </h4>
                                <LoadingButton
                                    onClick={handleSavePermissions}
                                    loading={saving}
                                    loadingText="Saqlanmoqda..."
                                    variant="white"
                                    size="sm"
                                >
                                    Saqlash
                                </LoadingButton>
                                    </div>

                                    <div className="space-y-6">
                                        {Object.entries(allPermissions).map(([module, permissions]) => (
                                            <div key={module}>
                                                <h5 className="text-md font-medium text-gray-900 mb-3 capitalize">
                                                    {module} moduli
                                                </h5>
                                                <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    {permissions.map((permission) => (
                                                        <label
                                                            key={permission.id}
                                                            className="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                checked={selectedPermissions.includes(permission.id)}
                                                                onChange={() => handlePermissionToggle(permission.id)}
                                                                className="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                            />
                                                            <span className="ml-3 text-sm text-gray-900">
                                                                {permission.name}
                                                            </span>
                                                        </label>
                                                    ))}
                                                </div>
                                            </div>
                                        ))}
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
