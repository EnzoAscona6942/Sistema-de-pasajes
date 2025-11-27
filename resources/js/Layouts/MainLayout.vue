<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Menú simple
const items = [
  { title: 'Inicio', to: '/' },
  { title: 'Mis Pasajes', to: '/bookings' },
];
</script>

<template>
  <v-app>
    <!-- Navbar -->
    <v-app-bar color="indigo-darken-4" elevation="2">
      <v-container class="d-flex align-center py-0">
        <v-app-bar-title class="font-weight-bold">
          <Link href="/" class="text-white text-decoration-none">
          🚌 UTN-BUS
          </Link>
        </v-app-bar-title>

        <v-spacer></v-spacer>

        <!-- Desktop Menu -->
        <div class="d-none d-md-flex align-center gap-4">
          <Link v-for="item in items" :key="item.title" :href="item.to" as="div">
          <v-btn variant="text" color="white">{{ item.title }}</v-btn>
          </Link>

          <div v-if="user" class="ml-4">
            <v-avatar color="indigo-lighten-4" size="32" class="mr-2">
              <span class="text-subtitle-2">{{ user.name.charAt(0) }}</span>
            </v-avatar>
          </div>

          <div v-else>
            <Link href="/login" as="div"><v-btn variant="outlined" color="white">Ingresar</v-btn></Link>
          </div>
        </div>
        <div v-if="user">
          <link href="/profile" class="text-white">
          <v-btn variant="outlined" color="white">{{ user.name }}</v-btn>
          </link>
        </div>
      </v-container>
    </v-app-bar>

    <!-- Contenido Principal -->
    <v-main class="bg-grey-lighten-4">
      <slot />
    </v-main>

    <v-footer app class="bg-indigo-darken-2 text-center d-flex justify-center">
      <span class="text-caption text-white">&copy; {{ new Date().getFullYear() }} UTN System</span>
    </v-footer>
  </v-app>
</template>