<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Ahora recibimos 'filters' desde Laravel, pero NO 'trips' (los buscamos nosotros)
const props = defineProps({ 
    filters: Object 
});

const trips = ref([]);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
    try {
        // Llamada a TU API existente
        // Mapeamos los nombres: tu vista usa 'date_departure' pero tu API usa 'date'
        const apiParams = {
            origin_id: props.filters.origin_id,
            destination_id: props.filters.destination_id,
            date: props.filters.date_departure // Adaptador Frontend -> API
        };

        const response = await axios.get('/api/v1/trips/search', { params: apiParams });
        
        // Tu API devuelve { data: [...] }
        trips.value = response.data.data;
    } catch (err) {
        console.error("Error cargando viajes", err);
        error.value = "Ocurrió un error al cargar los viajes.";
    } finally {
        loading.value = false;
    }
});
</script>

<template>
  <MainLayout>
    <v-container class="py-8">
      
      <div class="mb-6">
        <h1 class="text-h5 font-weight-bold text-grey-darken-3">Resultados de tu búsqueda</h1>
      </div>

      <!-- SKELETON LOADER (Mientras carga la API) -->
      <div v-if="loading">
          <v-skeleton-loader v-for="n in 3" :key="n" type="article" class="mb-4 border rounded-lg"></v-skeleton-loader>
      </div>

      <!-- ERROR -->
      <v-alert v-else-if="error" type="error" variant="tonal">{{ error }}</v-alert>

      <!-- EMPTY STATE -->
      <v-alert 
        v-else-if="trips.length === 0" 
        type="info" 
        variant="tonal" 
        icon="mdi-bus-alert"
      >
        No se encontraron servicios para esta fecha.
        <div class="mt-2"><Link href="/"><v-btn variant="text">Nueva búsqueda</v-btn></Link></div>
      </v-alert>

      <!-- LISTADO (Datos reales de la API) -->
      <v-row v-else>
        <v-col v-for="trip in trips" :key="trip.trip_id" cols="12">
          <v-card elevation="2" class="rounded-lg border overflow-hidden hover-card">
    <div class="d-flex flex-column flex-md-row"> <!-- CLAVE: Columna en móvil, Fila en Desktop -->
        
        <!-- 1. Izquierda: Empresa (Arriba en móvil) -->
        <div class="d-flex flex-row flex-md-column justify-space-between justify-md-center align-center px-4 py-3 bg-grey-lighten-4" 
             style="min-width: 140px; border-left: 6px solid #3F51B5;">
            <!-- Icono y Nombre -->
            <div class="d-flex flex-md-column align-center">
                <v-icon size="32" color="indigo-lighten-2" class="mr-2 mr-md-0 mb-md-2">mdi-bus-side</v-icon>
                <div class="text-caption font-weight-bold text-uppercase">{{ trip.company }}</div>
            </div>
            <!-- Chip Servicio -->
            <v-chip size="x-small" color="indigo" variant="flat" class="text-uppercase font-weight-bold ml-2 ml-md-0 mt-md-2">
                {{ trip.service_type }}
            </v-chip>
        </div>

        <!-- 2. Centro: Info Viaje -->
        <div class="flex-grow-1 pa-4">
            <div class="d-flex flex-column flex-sm-row align-center justify-space-between gap-4">
                
                <!-- Salida -->
                <div class="text-center w-100 w-sm-auto">
                    <div class="text-h4 font-weight-bold text-grey-darken-3">{{ trip.departure_time }}</div>
                    <div class="text-caption text-truncate" style="max-width: 120px;">{{ trip.origin }}</div>
                </div>

                <!-- Duración (Flecha) -->
                <div class="d-flex flex-column align-center px-2 w-100">
                    <div class="text-caption text-grey mb-1">{{ trip.duration }}</div>
                    <!-- Ocultamos la linea en moviles muy chicos -->
                    <v-divider class="border-opacity-50 w-100 border-indigo d-none d-sm-block" :thickness="2"></v-divider>
                    <v-icon class="d-sm-none" color="grey">mdi-arrow-down</v-icon>
                    <v-icon size="small" color="indigo" class="d-none d-sm-block" style="margin-top: -10px;">mdi-chevron-right</v-icon>
                </div>

                <!-- Llegada -->
                <div class="text-center w-100 w-sm-auto">
                    <div class="text-h5 font-weight-bold text-grey-darken-1">{{ trip.arrival_time }}</div>
                    <div class="text-caption text-truncate" style="max-width: 120px;">{{ trip.destination }}</div>
                </div>
            </div>
        </div>

        <!-- 3. Derecha: Precio y Acción (Abajo en móvil) -->
        <div class="pa-4 text-center text-md-right bg-white border-t border-md-t-0 border-md-s" style="min-width: 180px;">
            <div class="d-flex flex-row flex-md-column justify-space-between align-center h-100">
                
                <div class="text-left text-md-right">
                    <div class="text-caption text-grey mb-0 mb-md-1">Precio final</div>
                    <div class="text-h5 font-weight-black text-green-darken-1">
                        ${{ trip.price_preview }}
                    </div>
                </div>
                
                <Link :href="route('trips.show', { trip: trip.trip_id, passengers: 1 })" as="div" class="w-50 w-md-100 ml-4 ml-md-0">
                    <v-btn color="deep-orange-darken-1" block class="text-white text-capitalize font-weight-bold">
                        Seleccionar
                    </v-btn>
                </Link>
            </div>
        </div>

    </div>
</v-card>
        </v-col>
      </v-row>

    </v-container>
  </MainLayout>
</template>