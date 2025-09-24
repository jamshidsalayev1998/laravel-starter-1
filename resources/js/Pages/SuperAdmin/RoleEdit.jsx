import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import LoadingButton from '@/Components/LoadingButton';

export default function RoleEdit({ role }) {
    const [name, setName] = useState(role?.name || '');
    const [loading, setLoading] = useState(false);

    const handleSubmit = (e) => {
        e.preventDefault();
        setLoading(true);
        router.patch(`/super-admin/roles/${role.id}`, { name }, {
            onFinish: () => setLoading(false)
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Rolni tahrirlash
                </h2>
            }
        >
            <Head title="Rolni tahrirlash" />

            <div className="py-6">
                <div className="mx-auto  sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <form onSubmit={handleSubmit} className="p-6 space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Rol nomi
                                </label>
                                <input
                                    type="text"
                                    value={name}
                                    onChange={(e) => setName(e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    required
                                />
                            </div>
                            <div className="flex justify-end">
                                <LoadingButton type="submit" loading={loading} variant="white" size="sm">
                                    Saqlash
                                </LoadingButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}


