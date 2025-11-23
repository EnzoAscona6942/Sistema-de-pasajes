<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ trips: Array });
</script>

<template>
  <MainLayout>
    <v-container class="py-8">
      <h2 class="text-h5 mb-6 font-weight-bold text-indigo">Resultados de tu búsqueda</h2>

      <v-alert v-if="trips.length === 0" type="info" variant="tonal">
        No se encontraron viajes para esta fecha. Intenta con otro día.
      </v-alert>

      <v-row>
        <v-col v-for="trip in trips" :key="trip.id" cols="12">
          <v-card class="d-flex flex-wrap align-center pa-4" elevation="2" border>
            
            <!-- Horarios -->
            <div class="d-flex flex-column align-center px-4 text-center" style="min-width: 120px;">
              <span class="text-h5 font-weight-bold">{{ trip.departure_time_formatted }}</span>
              <v-icon color="grey">mdi-arrow-down</v-icon>
              <span class="text-body-1 text-grey-darken-1">{{ trip.arrival_time_formatted }}</span>
            </div>

            <!-- Info Empresa -->
            <div class="flex-grow-1 px-4 border-start border-md-0 mt-4 mt-md-0">
              <div class="text-h6 font-weight-bold">{{ trip.company_name }}</div>
              <v-chip size="small" color="indigo" class="mt-1">{{ trip.service_type }}</v-chip>
              <div class="text-caption text-grey mt-1">
                 Duración estimada: {{ trip.duration }}
              </div>
            </div>

            <!-- Precio y Acción -->
            <div class="d-flex flex-column align-end px-4 mt-4 mt-md-0 text-right w-100 w-md-auto">
              <div class="text-caption text-grey">Desde</div>
              <div class="text-h5 font-weight-bold text-success mb-3">
                ${{ trip.price_preview }}
              </div>
              
              <Link :href="route('trips.show', trip.id)">
                <v-btn color="indigo" prepend-icon="mdi-seat">
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