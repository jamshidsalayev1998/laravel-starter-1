import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import LoadingButton from '@/Components/LoadingButton';
import DropdownActions from '@/Components/DropdownActions';
import DropdownActionItem from '@/Components/DropdownActionItem';

export default function PermissionManagement({ permissions }) {
    const [newPermission, setNewPermission] = useState({
        name: '',
        module: ''
    });
    const [bulkPermissions, setBulkPermissions] = useState('');

    const [creating, setCreating] = useState(false);
    const handleCreatePermission = () => {
        if (newPermission.name && newPermission.module) {
            setCreating(true);
            router.post('/super-admin/permissions', {
                name: `${newPermission.module}.${newPermission.name}`,
                module: newPermission.module
            }, {
                onFinish: () => setCreating(false)
            });
            setNewPermission({ name: '', module: '' });
        }
    };

    const [bulkLoading, setBulkLoading] = useState(false);
    const handleBulkCreate = () => {
        if (bulkPermissions.trim()) {
            const permissionNames = bulkPermissions
                .split('\n')
                .map(line => line.trim())
                .filter(line => line.length > 0);

            setBulkLoading(true);
            router.post('/super-admin/permissions/bulk', {
                permissions: permissionNames
            }, {
                onFinish: () => setBulkLoading(false)
            });
            setBulkPermissions('');
        }
    };

    const handleDeletePermission = (permissionId, permissionName) => {
        if (confirm(`"${permissionName}" ruxsatini o'chirishni xohlaysizmi?`)) {
            router.delete(`/super-admin/permissions/${permissionId}`);
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Ruxsatlar boshqaruvi
                    </h2>
                    <Link
                        href="/super-admin/roles"
                        className="inline-flex items-center justify-center h-8 px-3 text-xs font-medium rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 shadow-sm"
                    >
                        Rollarga qaytish
                    </Link>
                </div>
            }
        >
            <Head title="Ruxsatlar boshqaruvi" />

            <div className="py-12">
                <div className="mx-auto  sm:px-6 lg:px-8">
                    {/* Create Permission */}
                    <div className="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h3 className="text-lg font-medium text-gray-900 mb-4">
                                Yangi ruxsat yaratish
                            </h3>
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Modul
                                    </label>
                                    <input
                                        type="text"
                                        value={newPermission.module}
                                        onChange={(e) => setNewPermission(prev => ({ ...prev, module: e.target.value }))}
                                        placeholder="masalan: users"
                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Ruxsat nomi
                                    </label>
                                    <input
                                        type="text"
                                        value={newPermission.name}
                                        onChange={(e) => setNewPermission(prev => ({ ...prev, name: e.target.value }))}
                                        placeholder="masalan: create"
                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    />
                                </div>
                                <div className="flex items-end">
                                    <LoadingButton
                                        onClick={handleCreatePermission}
                                        loading={creating}
                                        loadingText="Yaratilmoqda..."
                                        variant="white"
                                        size="sm"
                                        className="w-full"
                                    >
                                        Yaratish
                                    </LoadingButton>
                                </div>
                            </div>
                            <div className="mt-2 text-sm text-gray-500">
                                Natija: {newPermission.module && newPermission.name ? `${newPermission.module}.${newPermission.name}` : '...'}
                            </div>
                        </div>
                    </div>

                    {/* Bulk Create */}
                    <div className="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h3 className="text-lg font-medium text-gray-900 mb-4">
                                Ko'p ruxsat yaratish
                            </h3>
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Ruxsatlar (har birini alohida qatorda yozing)
                                    </label>
                                    <textarea
                                        value={bulkPermissions}
                                        onChange={(e) => setBulkPermissions(e.target.value)}
                                        placeholder="users.create&#10;users.edit&#10;users.delete&#10;posts.view&#10;posts.create"
                                        rows={6}
                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    />
                                </div>
                                <LoadingButton
                                    onClick={handleBulkCreate}
                                    loading={bulkLoading}
                                    loadingText="Yaratilmoqda..."
                                    variant="white"
                                    size="sm"
                                >
                                    Ko'p yaratish
                                </LoadingButton>
                            </div>
                        </div>
                    </div>

                    {/* Permissions by Module */}
                    <div className="space-y-6">
                        {Object.entries(permissions).map(([module, modulePermissions]) => (
                            <div key={module} className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <h3 className="text-lg font-medium text-gray-900 mb-4 capitalize">
                                        {module} moduli ({modulePermissions.length} ta ruxsat)
                                    </h3>
                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        {modulePermissions.map((permission) => (
                                            <div
                                                key={permission.id}
                                                className="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50"
                                            >
                                                <span className="text-sm text-gray-900">
                                                    {permission.name}
                                                </span>
                                                <div className="flex items-center space-x-2">
                                                    <span className="text-xs text-gray-500">
                                                        ID: {permission.id}
                                                    </span>
                                                    <DropdownActions>
                                                        <DropdownActionItem
                                                            onClick={() => handleDeletePermission(permission.id, permission.name)}
                                                            variant="danger"
                                                        >
                                                            O'chirish
                                                        </DropdownActionItem>
                                                    </DropdownActions>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
