<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm } from '@inertiajs/vue3';

// Props: Lista de ciudades enviadas desde Laravel
defineProps({ locations: Array });

const form = useForm({
  origin_id: null,
  destination_id: null,
  date: new Date().toISOString().substr(0, 10),
});

const submit = () => {
  // Enviamos a la ruta que muestra los resultados
  form.get(route('trips.index'));
};
</script>

<template>
  <MainLayout>
    <!-- Hero Section -->
    <v-sheet color="indigo" height="400" class="d-flex align-center justify-center position-relative">
      <div class="text-center z-index-10 px-4">
        <h1 class="text-h3 font-weight-bold text-white mb-4">Viaja por todo el país</h1>
        <p class="text-h6 text-white text-opacity-75">Los mejores precios en un solo lugar</p>
      </div>
    </v-sheet>

    <!-- Buscador Flotante -->
    <v-container style="margin-top: -60px; position: relative; z-index: 20;">
      <v-card elevation="8" class="pa-6 rounded-lg">
        <v-form @submit.prevent="submit">
          <v-row align="center">
            <v-col cols="12" md="3">
              <v-autocomplete
                v-model="form.origin_id"
                :items="locations"
                item-title="name"
                item-value="id"
                label="Origen"
                prepend-inner-icon="mdi-map-marker"
                variant="outlined"
                density="comfortable"
                color="indigo"
              ></v-autocomplete>
            </v-col>

            <v-col cols="12" md="3">
              <v-autocomplete
                v-model="form.destination_id"
                :items="locations"
                item-title="name"
                item-value="id"
                label="Destino"
                prepend-inner-icon="mdi-flag"
                variant="outlined"
                density="comfortable"
                color="indigo"
              ></v-autocomplete>
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="form.date"
                type="date"
                label="Fecha de salida"
                variant="outlined"
                density="comfortable"
                color="indigo"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="3">
              <v-btn 
                block 
                color="warning" 
                size="x-large" 
                type="submit"
                :loading="form.processing"
              >
                Buscar Pasajes
              </v-btn>
            </v-col>
          </v-row>
        </v-form>
      </v-card>
    </v-container>
  </MainLayout>
</template>