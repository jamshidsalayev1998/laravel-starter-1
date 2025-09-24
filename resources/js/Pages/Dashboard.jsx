import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Dashboard({ auth }) {
    const { user } = auth;

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto  sm:px-6 lg:px-8">
                    {/* Welcome Card */}
                    <div className="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h3 className="text-lg font-medium text-gray-900 mb-4">
                                Xush kelibsiz, {user.name}!
                            </h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm text-gray-600">Telefon:</p>
                                    <p className="font-medium">{user.phone}</p>
                                </div>
                                {user.email && (
                                    <div>
                                        <p className="text-sm text-gray-600">Email:</p>
                                        <p className="font-medium">{user.email}</p>
                                    </div>
                                )}
                                <div>
                                    <p className="text-sm text-gray-600">Rol:</p>
                                    <p className="font-medium">
                                        {user.roles?.map(role => role.name).join(', ') || 'Foydalanuvchi'}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600">Holat:</p>
                                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                        user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    }`}>
                                        {user.is_active ? 'Faol' : 'Nofaol'}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Quick Actions */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {/* Profile Management */}
                        <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <h4 className="text-lg font-medium text-gray-900 mb-2">
                                    Profil boshqaruvi
                                </h4>
                                <p className="text-sm text-gray-600 mb-4">
                                    Profil ma'lumotlarini yangilash va parolni o'zgartirish
                                </p>
                                <Link
                                    href={route('profile.edit')}
                                    className="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    Profilni tahrirlash
                                </Link>
                            </div>
                        </div>

                        {/* User Management (for admins) */}
                        {user.roles?.some(role => ['super-admin', 'admin'].includes(role.name)) && (
                            <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <h4 className="text-lg font-medium text-gray-900 mb-2">
                                        Foydalanuvchilar boshqaruvi
                                    </h4>
                                    <p className="text-sm text-gray-600 mb-4">
                                        Foydalanuvchilarni ko'rish, ban qilish va faollashtirish
                                    </p>
                                    <Link
                                        href="/admin/users"
                                        className="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        Foydalanuvchilar
                                    </Link>
                                </div>
                            </div>
                        )}

                        {/* Role & Permission Management (for super-admin) */}
                        {user.roles?.some(role => role.name === 'super-admin') && (
                            <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <h4 className="text-lg font-medium text-gray-900 mb-2">
                                        Rollar va ruxsatlar
                                    </h4>
                                    <p className="text-sm text-gray-600 mb-4">
                                        Rollar va ruxsatlarni boshqarish
                                    </p>
                                    <Link
                                        href="/super-admin/roles"
                                        className="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        Rollar
                                    </Link>
                                </div>
                            </div>
                        )}

                        {/* Device Management */}
                        <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <h4 className="text-lg font-medium text-gray-900 mb-2">
                                    Qurilmalar boshqaruvi
                                </h4>
                                <p className="text-sm text-gray-600 mb-4">
                                    Faol qurilmalarni ko'rish va boshqarish
                                </p>
                                <Link
                                    href="/devices"
                                    className="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    Qurilmalar
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
