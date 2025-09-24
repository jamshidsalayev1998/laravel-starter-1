import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import LoadingButton from '@/Components/LoadingButton';

export default function UserAccess({ user, roles, permissions, grantedViaRoles, directPermissionIds }) {
    const [selectedRole, setSelectedRole] = useState('');
    const [selectedPermission, setSelectedPermission] = useState('');
    const [loading, setLoading] = useState(false);

    const attachRole = () => {
        if (!selectedRole) return;
        setLoading(true);
        router.post(`/super-admin/users/${user.id}/roles/attach`, { role_id: Number(selectedRole) }, { onFinish: () => setLoading(false) });
    };

    const detachRole = (roleId) => {
        setLoading(true);
        router.post(`/super-admin/users/${user.id}/roles/detach`, { role_id: Number(roleId) }, { onFinish: () => setLoading(false) });
    };

    const grantPermission = () => {
        if (!selectedPermission) return;
        // Agar bu permission role orqali berilgan bo'lsa, to'g'ridan-to'g'ri bermaymiz
        if (grantedViaRoles.includes(Number(selectedPermission))) return;
        setLoading(true);
        router.post(`/super-admin/users/${user.id}/permissions/grant`, { permission_id: Number(selectedPermission) }, { onFinish: () => setLoading(false) });
    };

    const revokePermission = (permissionId) => {
        // Faqat bevosita berilgan permissions qaytarib olinadi
        if (!directPermissionIds.includes(Number(permissionId))) return;
        setLoading(true);
        router.post(`/super-admin/users/${user.id}/permissions/revoke`, { permission_id: Number(permissionId) }, { onFinish: () => setLoading(false) });
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Foydalanuvchi roli va ruxsatlari</h2>}
        >
            <Head title={`${user.name} - Access`} />
            <div className="py-6">
                <div className="mx-auto  sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Roles */}
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6 space-y-4">
                            <h3 className="text-lg font-medium">Rollar</h3>
                            <div className="flex gap-2">
                                <select className="flex-1 border rounded-md px-2 py-2" value={selectedRole} onChange={e => setSelectedRole(e.target.value)}>
                                    <option value="">Rol tanlang</option>
                                    {roles.map(r => (
                                        <option key={r.id} value={r.id}>{r.name}</option>
                                    ))}
                                </select>
                                <LoadingButton onClick={attachRole} loading={loading} variant="white" size="sm">Biriktirish</LoadingButton>
                            </div>
                            <div className="space-y-2">
                                {user.roles?.map(r => (
                                    <div key={r.id} className="flex items-center justify-between border rounded-md px-3 py-2">
                                        <span>{r.name}</span>
                                        <LoadingButton onClick={() => detachRole(r.id)} loading={loading} variant="white" size="xs">Olish</LoadingButton>
                                    </div>
                                ))}
                                {(!user.roles || user.roles.length === 0) && <p className="text-sm text-gray-500">Rol biriktirilmagan</p>}
                            </div>
                        </div>
                    </div>

                    {/* Permissions */}
                    <div className="lg:col-span-2 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6 space-y-6">
                            <h3 className="text-lg font-medium">Ruxsatlar</h3>
                            <div className="flex gap-2">
                                <select className="flex-1 border rounded-md px-2 py-2" value={selectedPermission} onChange={e => setSelectedPermission(e.target.value)}>
                                    <option value="">Ruxsat tanlang</option>
                                    {Object.entries(permissions).map(([module, perms]) => (
                                        <optgroup key={module} label={module}>
                                            {perms.map(p => (
                                                <option key={p.id} value={p.id} disabled={grantedViaRoles.includes(p.id)}>
                                                    {p.name}{grantedViaRoles.includes(p.id) ? ' (rol orqali)' : ''}
                                                </option>
                                            ))}
                                        </optgroup>
                                    ))}
                                </select>
                                <LoadingButton onClick={grantPermission} loading={loading} variant="white" size="sm">Berish</LoadingButton>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                {Object.entries(permissions).map(([module, perms]) => (
                                    <div key={module} className="border rounded-md">
                                        <div className="px-3 py-2 font-medium bg-gray-50">{module}</div>
                                        <div className="p-3 space-y-2">
                                            {perms.map(p => {
                                                const viaRole = grantedViaRoles.includes(p.id);
                                                const direct = directPermissionIds.includes(p.id);
                                                return (
                                                    <div key={p.id} className="flex items-center justify-between text-sm">
                                                        <div>
                                                            <span className="mr-2">{p.name}</span>
                                                            {viaRole && <span className="text-xs text-gray-500">(rol orqali)</span>}
                                                        </div>
                                                        {direct ? (
                                                            <LoadingButton onClick={() => revokePermission(p.id)} loading={loading} variant="white" size="xs">Olish</LoadingButton>
                                                        ) : (
                                                            <LoadingButton onClick={() => { setSelectedPermission(String(p.id)); grantPermission(); }} loading={loading} variant="white" size="xs" disabled={viaRole}>Berish</LoadingButton>
                                                        )}
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}


