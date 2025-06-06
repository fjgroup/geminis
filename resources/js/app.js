import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        console.log('Initial $page.props:', JSON.stringify(props, null, 2)); // Añadir esto
        try {
            const app = createApp({ render: () => h(App, props) });
            app.use(plugin);
            app.use(ZiggyVue);
            app.mount(el);
            return app;
        } catch (e) {
            console.error('Error during app setup/mount:', e);
            console.error('Props at time of error:', JSON.stringify(props, null, 2));
            // Opcionalmente, re-lanzar el error o mostrar un mensaje en la página
            el.innerHTML = '<h1>Error al iniciar la aplicación</h1><pre>' + e.stack + '</pre><pre>Props: ' + JSON.stringify(props, null, 2) + '</pre>';
            throw e; // Re-lanzar para ver el error original en la consola también
        }
    },
    progress: {
        color: '#4B5563',
    },
});
