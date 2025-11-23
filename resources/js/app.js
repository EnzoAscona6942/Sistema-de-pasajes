import './bootstrap';
import '../css/app.css'; // Tu CSS global (Tailwind o custom)

// Importar estilos de iconos y Vuetify
import '@mdi/font/css/materialdesignicons.css';
import 'vuetify/styles';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy'; // Rutas route('name')

// Configuración de Vuetify
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { es } from 'vuetify/locale';

const vuetify = createVuetify({
    components,
    directives,
    locale: {
        locale: 'es',
        messages: { es },
    },
    theme: {
        defaultTheme: 'light',
        themes: {
            light: {
                colors: {
                    primary: '#3F51B5', // Indigo
                    secondary: '#5C6BC0',
                    accent: '#82B1FF',
                    error: '#FF5252',
                    info: '#2196F3',
                    success: '#4CAF50',
                    warning: '#FFC107',
                },
            },
        },
    },
});

const appName = import.meta.env.VITE_APP_NAME || 'Plataforma 10 Clone';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue) // Permite usar route('trips.index') en Vue
            .use(vuetify)  // Inyectamos Vuetify
            .mount(el);
    },
    progress: {
        color: '#4CAF50', // Barra de carga verde superior al navegar
    },
});