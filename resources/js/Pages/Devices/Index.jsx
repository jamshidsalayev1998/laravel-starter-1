import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function DevicesIndex({ title }) {
    return (
        <AuthenticatedLayout
        header={
            <h2 className="text-xl font-semibold leading-tight text-gray-800">
                Qurilmalar boshqaruvi
            </h2>
        }
        >
            <Head title={title} />

            <div className="py-1">
                <div className="mx-auto  sm:px-6 ">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h1 className="text-2xl font-bold mb-6">{title}</h1>

                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                {/* Qurilma kartasi */}
                                <div className="bg-gray-50 p-6 rounded-lg border">
                                    <div className="flex items-center justify-between mb-4">
                                        <h3 className="text-lg font-semibold">iPhone 15 Pro</h3>
                                        <span className="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                            Faol
                                        </span>
                                    </div>
                                    <p className="text-gray-600 text-sm mb-4">
                                        iOS 17.2 • 256GB • Space Black
                                    </p>
                                    <div className="flex justify-between items-center">
                                        <span className="text-sm text-gray-500">Oxirgi faollik: 2 soat oldin</span>
                                        <button className="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            Batafsil
                                        </button>
                                    </div>
                                </div>

                                {/* Qurilma kartasi */}
                                <div className="bg-gray-50 p-6 rounded-lg border">
                                    <div className="flex items-center justify-between mb-4">
                                        <h3 className="text-lg font-semibold">Samsung Galaxy S24</h3>
                                        <span className="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                            Nofaol
                                        </span>
                                    </div>
                                    <p className="text-gray-600 text-sm mb-4">
                                        Android 14 • 128GB • Titanium Gray
                                    </p>
                                    <div className="flex justify-between items-center">
                                        <span className="text-sm text-gray-500">Oxirgi faollik: 1 kun oldin</span>
                                        <button className="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            Batafsil
                                        </button>
                                    </div>
                                </div>

                                {/* Qurilma kartasi */}
                                <div className="bg-gray-50 p-6 rounded-lg border">
                                    <div className="flex items-center justify-between mb-4">
                                        <h3 className="text-lg font-semibold">iPad Pro 12.9"</h3>
                                        <span className="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                            Faol
                                        </span>
                                    </div>
                                    <p className="text-gray-600 text-sm mb-4">
                                        iPadOS 17.2 • 512GB • Space Gray
                                    </p>
                                    <div className="flex justify-between items-center">
                                        <span className="text-sm text-gray-500">Oxirgi faollik: 30 daqiqa oldin</span>
                                        <button className="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            Batafsil
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {/* Qo'shish tugmasi */}
                            <div className="mt-8">
                                <button className="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                                    + Yangi qurilma qo'shish
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
