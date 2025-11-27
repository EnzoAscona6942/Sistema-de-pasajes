
<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';


// Props recibidos desde Laravel
defineProps({ locations: Array });

// Formulario reactivo de Inertia
const form = useForm({
  origin_id: null,
  destination_id: null,
  trip_type: 'one_way', // 'one_way' o 'round_trip'
  date_departure: new Date().toISOString().substr(0, 10),
  date_return: null,
  passengers: 1,
  accommodation: false
});

// Lógica de intercambio (Swap) de origen/destino
const swapLocations = () => {
  const temp = form.origin_id;
  form.origin_id = form.destination_id;
  form.destination_id = temp;
};

// Función de búsqueda
const submit = () => {
  form.get(route('trips.index'));
};

// Iconos de pestañas superiores (Decorativo)
const activeTab = ref(0);
const tabs = [
    { text: 'Omnibus', icon: 'mdi-bus' },
    { text: 'Fluviales', icon: 'mdi-ferry' },
    { text: 'Transfer', icon: 'mdi-van-passenger' },
    { text: 'Turismo', icon: 'mdi-map-marker-radius' }
];
</script>

<template>
  <MainLayout>
    <div class="bg-white border-b mb-6">
        <v-container class="py-0">
            <v-tabs v-model="activeTab" color="indigo-darken-4" align-tabs="center">
                <v-tab v-for="(tab, index) in tabs" :key="index" :value="index" class="text-capitalize">
                    <v-icon start>{{ tab.icon }}</v-icon> {{ tab.text }}
                </v-tab>
            </v-tabs>
        </v-container>
    </div>

    <v-container>
      <v-row>
        <!-- COLUMNA IZQUIERDA: EL BUSCADOR -->
        <v-col cols="12" md="5" lg="4">
          <v-card elevation="4" class="rounded-lg pa-4">
            <h2 class="text-h6 font-weight-bold mb-4 text-grey-darken-3">
              Buscá tus pasajes en micro
            </h2>

            <v-form @submit.prevent="submit">
              
              <!-- 1. Origen -->
              <div class="mb-1 font-weight-medium text-caption text-grey-darken-1">Origen*</div>
              <v-autocomplete
                v-model="form.origin_id"
                :items="locations"
                item-title="title"
                item-value="value"
                variant="outlined"
                density="compact"
                placeholder="Ingresá ciudad o terminal"
                prepend-inner-icon="mdi-map-marker-outline" 
                hide-details="auto"
                class="mb-3"
              ></v-autocomplete>

              <!-- Botón Swap (Flotante visualmente entre inputs) -->
              <div class="d-flex justify-end" style="height: 0; position: relative; top: -10px; z-index: 2;">
                  <v-btn 
                    icon="mdi-swap-vertical" 
                    size="small" 
                    color="indigo-darken-4" 
                    variant="elevated" 
                    class="mr-4"
                    @click="swapLocations"
                  ></v-btn>
              </div>

              <!-- 2. Destino -->
              <div class="mb-1 font-weight-medium text-caption text-grey-darken-1">Destino*</div>
              <v-autocomplete
                v-model="form.destination_id"
                :items="locations"
                item-title="title"
                item-value="value"
                variant="outlined"
                density="compact"
                placeholder="Ingresá ciudad o terminal"
                prepend-inner-icon="mdi-map-marker"
                hide-details="auto"
                class="mb-4"
              ></v-autocomplete>

              <!-- 3. Radio Buttons (Ida / Ida y vuelta) -->
              <v-radio-group v-model="form.trip_type" inline class="mb-2" density="compact" hide-details>
                <v-radio label="Sólo ida" value="one_way" color="primary"></v-radio>
                <v-radio label="Ida y vuelta" value="round_trip" color="primary"></v-radio>
              </v-radio-group>

              <!-- 4. Fechas (Grid anidado) -->
              <v-row dense>
                <v-col cols="6">
                    <div class="mb-1 font-weight-medium text-caption text-grey-darken-1">Partida*</div>
                    <v-text-field
                        v-model="form.date_departure"
                        type="date"
                        variant="outlined"
                        density="compact"
                        hide-details="auto"
                    ></v-text-field>
                </v-col>
                <v-col cols="6">
                    <div class="mb-1 font-weight-medium text-caption text-grey-darken-1">Regreso</div>
                    <v-text-field
                        v-model="form.date_return"
                        type="date"
                        variant="outlined"
                        density="compact"
                        :disabled="form.trip_type === 'one_way'"
                        hide-details="auto"
                    ></v-text-field>
                </v-col>
              </v-row>

              <!-- 5. Pasajeros -->
              <div class="mt-4 mb-1 font-weight-medium text-caption text-grey-darken-1">Pasajeros*</div>
              <v-text-field
                v-model="form.passengers"
                type="number"
                min="1"
                max="10"
                variant="outlined"
                density="compact"
                prepend-inner-icon="mdi-account-multiple-outline"
                hide-details
                class="mb-4"
              >
                <template v-slot:append-inner>
                    <div class="d-flex">
                        <v-btn size="x-small" variant="text" icon="mdi-minus" @click="form.passengers > 1 ? form.passengers-- : null"></v-btn>
                        <v-btn size="x-small" variant="text" icon="mdi-plus" @click="form.passengers < 10 ? form.passengers++ : null"></v-btn>
                    </div>
                </template>
              </v-text-field>

              <!-- 6. Checkbox Alojamiento -->
              <v-checkbox
                v-model="form.accommodation"
                label="Quiero buscar alojamiento"
                color="primary"
                density="compact"
                hide-details
                class="mb-4"
              ></v-checkbox>

              <!-- 7. Botón Submit (Color Naranja característico) -->
              <v-btn 
                block 
                color="indigo-darken-4" 
                size="x-large" 
                type="submit"
                :loading="form.processing"
                class="text-white font-weight-bold text-capitalize rounded-pill mt-2"
                elevation="2"
                variant="tonal"
              >
                Buscar pasajes
              </v-btn>

            </v-form>
          </v-card>
        </v-col>

        <!-- COLUMNA DERECHA: BANNER PROMOCIONAL -->
        <v-col cols="12" md="7" lg="8" class="d-none d-md-block">
            <v-card elevation="0" color="transparent" class="h-100 d-flex align-center justify-center">
                <!-- Reemplaza src con tu imagen real en /public/images/ -->
                <!-- Si no tienes imagen, este placeholder degradado se ve bien -->
                <v-img
                    src="/images/banner-home.jpg" 
                    class="rounded-lg shadow-lg"
                    cover
                    height="100%"
                    width="100%"
                    min-height="500"
                    gradient="to bottom right, rgba(255,87,34,0.8), rgba(255,193,7,0.8)"
                >
                    <template v-slot:placeholder>
                        <div class="d-flex align-center justify-center fill-height bg-deep-orange-lighten-1 text-white">
                            <div class="text-center">
                                <h1 class="text-h2 font-weight-black mb-4">VIAJÁ MÁS FÁCIL</h1>
                                <p class="text-h5">Descargá nuestra APP</p>
                                <v-icon size="80" class="mt-4">mdi-cellphone-link</v-icon>
                            </div>
                        </div>
                    </template>
                    
                    <!-- Si pones una imagen real, borra el contenido del template placeholder arriba y deja esto vacío o con contenido superpuesto -->
                </v-img>
            </v-card>
        </v-col>
      </v-row>

      <!-- SECCIÓN INFERIOR: DESTINOS (Opcional) -->
      <v-row class="mt-12">
        <v-col cols="12"><h3 class="text-h5 font-weight-bold">Viajes y promociones</h3></v-col>
        <v-col cols="12" sm="6" md="4" v-for="n in 3" :key="n">
            <v-card class="rounded-lg" elevation="2">
                <v-img src="https://picsum.photos/500/300" height="200" cover></v-img>
                <v-card-title class="text-subtitle-1 font-weight-bold">Destino Turístico {{ n }}</v-card-title>
                <v-card-text>Aprovechá los mejores precios para tu próxima escapada.</v-card-text>
            </v-card>
        </v-col>
      </v-row>

    </v-container>
  </MainLayout>
</template>

<style scoped>
/* Ajustes finos para parecerse a Plataforma 10 */
.v-field--variant-outlined .v-field__outline__start,
.v-field--variant-outlined .v-field__outline__end {
    border-color: #e0e0e0; 
}
.v-card {
    border: 1px solid #f0f0f0;
}
</style>