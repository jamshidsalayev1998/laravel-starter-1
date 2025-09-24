import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { ToastProvider } from './Components/ToastContainer';
import ErrorBoundary from './Components/ErrorBoundary';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <ErrorBoundary>
                <ToastProvider>
                    <App {...props} />
                </ToastProvider>
            </ErrorBoundary>
        );
    },
    progress: {
        color: '#4B5563',
    },
    // Browser history bilan ishlash uchun qo'shimcha konfiguratsiya
    onNavigate: (page, { preserveState, preserveScroll }) => {
        // Sahifa o'zgarishida state'ni saqlash
        preserveState(true);
        preserveScroll(true);
    },
    // Back/Forward tugmalari uchun
    onBackForward: (page, { preserveState, preserveScroll }) => {
        preserveState(true);
        preserveScroll(true);
    },
});
