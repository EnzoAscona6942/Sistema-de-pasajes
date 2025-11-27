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
          <v-card elevation="2" class="rounded-lg border d-flex flex-wrap align-center pa-0 overflow-hidden hover-card">
            
            <!-- Lado Izquierdo -->
            <div class="d-flex flex-column justify-center align-center px-4 py-4 bg-grey-lighten-4 h-100" style="min-width: 140px; border-left: 6px solid #3F51B5;">
               <v-icon size="40" color="indigo-lighten-2" class="mb-2">mdi-bus-side</v-icon>
               <div class="text-caption font-weight-bold text-center text-uppercase">{{ trip.company }}</div>
               <v-chip size="x-small" color="indigo" variant="flat" class="mt-2 text-uppercase font-weight-bold">
                 {{ trip.service_type }}
               </v-chip>
            </div>

            <!-- Centro -->
            <div class="flex-grow-1 pa-4">
               <div class="d-flex align-center justify-space-between mb-2" style="max-width: 400px;">
                  <div class="text-center">
                     <div class="text-h4 font-weight-bold text-grey-darken-3">{{ trip.departure_time }}</div>
                  </div>
                  <div class="d-flex flex-column align-center px-4 w-100">
                     <div class="text-caption text-grey mb-1">{{ trip.duration }}</div>
                     <v-divider class="border-opacity-50 w-100 border-indigo" :thickness="2"></v-divider>
                     <v-icon size="small" color="indigo" style="margin-top: -10px;">mdi-chevron-right</v-icon>
                  </div>
                  <div class="text-center">
                     <div class="text-h5 font-weight-bold text-grey-darken-1">{{ trip.arrival_time }}</div>
                  </div>
               </div>
            </div>

            <!-- Lado Derecho -->
            <div class="pa-4 text-right bg-white" style="min-width: 180px;">
               <div class="text-caption text-grey mb-1">Precio desde</div>
               <div class="text-h5 font-weight-black text-green-darken-1 mb-3">
                  ${{ trip.price_preview }}
               </div>
               
               <!-- Usamos trip.trip_id porque así lo devuelve tu API Controller -->
               <Link :href="route('trips.show', { trip: trip.trip_id, passengers: filters.passengers || 1 })" as="div">
                 <v-btn color="deep-orange-darken-1" block class="text-white text-capitalize font-weight-bold">
                    Seleccionar
                 </v-btn>
               </Link>
            </div>

          </v-card>
        </v-col>
      </v-row>

    </v-container>
  </MainLayout>
</template>