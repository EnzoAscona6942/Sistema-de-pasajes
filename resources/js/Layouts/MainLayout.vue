<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue'; // Importar ref
import GlobalAlerts from '@/Components/GlobalAlerts.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const drawer = ref(false); // Estado para el menú lateral móvil

const items = [
  { title: 'Inicio', to: '/', icon: 'mdi-home' },
  { title: 'Mis Pasajes', to: '/bookings', icon: 'mdi-ticket-account' },
];
</script>

<template>
  <v-app>
    <!-- NAVBAR -->
    <v-app-bar color="indigo-darken-4" elevation="2">
      <v-container class="d-flex align-center py-0">
        
        <!-- Icono Hamburguesa (Solo visible en móviles: d-md-none) -->
        <v-app-bar-nav-icon variant="text" @click.stop="drawer = !drawer" class="d-md-none"></v-app-bar-nav-icon>

        <v-app-bar-title class="font-weight-bold">
          <Link href="/" class="text-white text-decoration-none d-flex align-center">
             <!-- Icono visible solo en móvil para ahorrar espacio de texto -->
             <v-icon class="d-sm-none mr-2">mdi-bus</v-icon>
             <span class="d-none d-sm-block">🚌 UTN-BUS</span>
             <span class="d-sm-none">UTN</span>
          </Link>
        </v-app-bar-title>

        <v-spacer></v-spacer>

        <!-- MENU ESCRITORIO (Oculto en móviles: d-none d-md-flex) -->
        <div class="d-none d-md-flex align-center gap-4">
          <Link v-for="item in items" :key="item.title" :href="item.to" as="div">
            <v-btn variant="text" color="white" :prepend-icon="item.icon">{{ item.title }}</v-btn>
          </Link>

          <div v-if="user" class="ml-4 d-flex align-center">
             <Link href="/profile" as="div">
                <v-chip pill color="indigo-lighten-4" link>
                  <v-avatar start color="indigo-darken-1">
                    <span class="text-subtitle-2">{{ user.name.charAt(0) }}</span>
                  </v-avatar>
                  {{ user.name }}
                </v-chip>
             </Link>
          </div>
          <div v-else>
            <Link href="/login" as="div"><v-btn variant="outlined" color="white">Ingresar</v-btn></Link>
          </div>
        </div>

      </v-container>
    </v-app-bar>

    <!-- CAJÓN LATERAL (SOLO MÓVIL) -->
    <v-navigation-drawer v-model="drawer" temporary location="left">
        <v-list>
            <v-list-item title="Menú Principal" subtitle="Navegación"></v-list-item>
            <v-divider></v-divider>
            
            <div v-for="item in items" :key="item.title">
                <Link :href="item.to" as="div">
                    <v-list-item :prepend-icon="item.icon" :title="item.title" link></v-list-item>
                </Link>
            </div>
        </v-list>

        <template v-slot:append>
            <div class="pa-4">
                <div v-if="user">
                    <div class="text-subtitle-2 mb-2">Hola, {{ user.name }}</div>
                    <!-- Formulario de Logout compatible con Inertia -->
                    <Link href="/logout" method="post" as="div">
                        <v-btn block color="red-lighten-1" variant="tonal">Cerrar Sesión</v-btn>
                    </Link>
                </div>
                <div v-else>
                    <Link href="/login" as="div">
                        <v-btn block color="indigo" variant="flat">Iniciar Sesión</v-btn>
                    </Link>
                </div>
            </div>
        </template>
    </v-navigation-drawer>

    <v-main class="bg-grey-lighten-4">
      <slot />
    </v-main>

    <GlobalAlerts />
    
    <!-- Footer optimizado para móvil -->
    <v-footer app class="bg-indigo-darken-2 text-center d-flex flex-column justify-center py-2 text-caption">
      <div>&copy; {{ new Date().getFullYear() }} UTN System</div>
    </v-footer>
  </v-app>
</template>