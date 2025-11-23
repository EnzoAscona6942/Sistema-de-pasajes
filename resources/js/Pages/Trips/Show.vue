<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({
  trip: Object,           // Datos del viaje
  seatsByFloor: Object,   // Objeto { '1': [seats], '2': [seats] }
});

// Estado de selección
const selectedSeats = ref([]);

// Formulario para enviar la reserva
const form = useForm({
  trip_id: props.trip.id,
  bookings: [] // Array de { seat_id }
});
onMounted(() => {
    // Escuchar el canal 'trips.{id}'
    window.Echo.channel(`trips.${props.trip.id}`)
        .listen('.seat.booked', (e) => {
            console.log('Asiento reservado en tiempo real:', e.seatId);
            markSeatAsOccupied(e.seatId);
        });
});
onUnmounted(() => {
    window.Echo.leave(`trips.${props.trip.id}`);
});

// Función auxiliar para actualizar la UI sin recargar
const markSeatAsOccupied = (seatIdToMark) => {
    // props.seatsByFloor es un Objeto, debemos iterar sus claves (pisos)
    for (const floor in props.seatsByFloor) {
        const seats = props.seatsByFloor[floor];
        const seatIndex = seats.findIndex(s => s.seat_id === seatIdToMark);
        
        if (seatIndex !== -1) {
            // Vue 3 Reactivity: Actualizamos el estado directamente
            seats[seatIndex].status = 'occupied'; 
            
            // Si el usuario actual lo tenía seleccionado, se lo deseleccionamos
            const selectedIndex = selectedSeats.value.findIndex(s => s.seat_id === seatIdToMark);
            if (selectedIndex !== -1) {
                selectedSeats.value.splice(selectedIndex, 1);
                alert('¡Lo sentimos! Alguien acaba de ganar ese asiento.');
            }
        }
    }
};
// Alternar selección de asiento
const toggleSeat = (seat) => {
  if (seat.status === 'occupied') return;

  const index = selectedSeats.value.findIndex(s => s.seat_id === seat.seat_id);
  if (index === -1) {
    // Validar máximo de pasajes (ej: 4)
    if (selectedSeats.value.length >= 4) {
      alert('Máximo 4 pasajes por persona');
      return;
    }
    selectedSeats.value.push(seat);
  } else {
    selectedSeats.value.splice(index, 1);
  }
};

// Cálculo reactivo del total
const totalPrice = computed(() => {
  return selectedSeats.value.reduce((sum, seat) => sum + seat.price, 0);
});

// Enviar compra
const handleCheckout = () => {
  // Aquí podrías iterar para crear multiples reservas o manejar un array en el backend
  // Para el ejemplo simple, tomamos el primero, pero la lógica real debería ser un loop
  // o un endpoint batch.
  alert(`Implementar checkout para ${selectedSeats.value.length} pasajes. Total: $${totalPrice.value}`);
  
  // Ejemplo de lógica unitaria con tu BookingController
  form.post(route('bookings.store'), {
    preserveScroll: true,
    data: {
        trip_id: props.trip.id,
        seat_id: selectedSeats.value[0].seat_id, 
        user_id: 1 // ID Auth simulado
    }
  });
};

// Función para obtener color del asiento
const getSeatColor = (seat) => {
  if (seat.status === 'occupied') return 'grey-lighten-2';
  if (selectedSeats.value.find(s => s.seat_id === seat.seat_id)) return 'success';
  return 'white'; // Disponible
};

// Función para obtener color del borde/icono
const getSeatIconColor = (seat) => {
  if (seat.status === 'occupied') return 'grey';
  if (selectedSeats.value.find(s => s.seat_id === seat.seat_id)) return 'white';
  return 'indigo';
};

</script>

<template>
  <MainLayout>
    <v-container class="py-8">
      
      <v-row>
        <!-- COLUMNA IZQUIERDA: EL COLECTIVO -->
        <v-col cols="12" md="7" lg="6">
          <v-card class="pa-4 bg-grey-lighten-3" elevation="0" border>
            <div class="d-flex align-center mb-4 text-grey-darken-2">
               <v-icon class="mr-2">mdi-steering</v-icon> Frente del vehículo
            </div>

            <!-- Iteramos por pisos -->
            <div v-for="(seats, floor) in seatsByFloor" :key="floor" class="mb-8">
              <h3 class="text-subtitle-1 font-weight-bold mb-2 text-indigo">
                Piso {{ floor }}
              </h3>
              
              <!-- CONTENEDOR DEL BUS (Grid CSS Personalizado) -->
              <div class="bus-grid-container paper-elevation-2 rounded bg-white pa-4 border">
                
                <div 
                  v-for="seat in seats" 
                  :key="seat.seat_id"
                  class="seat-wrapper d-flex justify-center"
                  :class="{ 'is-occupied': seat.status === 'occupied' }"
                >
                  <v-tooltip location="top">
                    <template v-slot:activator="{ props }">
                      <v-btn
                        v-bind="props"
                        :color="getSeatColor(seat)"
                        class="seat-btn"
                        elevation="2"
                        width="45"
                        height="45"
                        :disabled="seat.status === 'occupied'"
                        @click="toggleSeat(seat)"
                        icon
                      >
                        <v-icon :color="getSeatIconColor(seat)" size="24">
                            mdi-seat-recline-extra
                        </v-icon>
                        
                        <!-- Número de asiento flotante -->
                        <span class="seat-number">{{ seat.number }}</span>
                      </v-btn>
                    </template>
                    <span>Asiento {{ seat.number }} - ${{ seat.price }} <br/> {{ seat.type }}</span>
                  </v-tooltip>
                </div>

              </div>
            </div>

            <!-- Referencias -->
            <div class="d-flex justify-center gap-4 mt-4 text-caption">
                <div class="d-flex align-center"><v-sheet color="white" width="16" height="16" class="border mr-1 rounded"></v-sheet> Libre</div>
                <div class="d-flex align-center"><v-sheet color="success" width="16" height="16" class="mr-1 rounded"></v-sheet> Seleccionado</div>
                <div class="d-flex align-center"><v-sheet color="grey-lighten-2" width="16" height="16" class="mr-1 rounded"></v-sheet> Ocupado</div>
            </div>
          </v-card>
        </v-col>

        <!-- COLUMNA DERECHA: RESUMEN -->
        <v-col cols="12" md="5" lg="4" offset-lg="1">
           <v-card class="position-sticky" style="top: 20px;" elevation="4">
              <v-card-item class="bg-indigo text-white py-4">
                 <v-card-title>Tu Resumen</v-card-title>
                 <v-card-subtitle class="text-indigo-lighten-4">
                    {{ trip.origin.name }} -> {{ trip.destination.name }}
                 </v-card-subtitle>
              </v-card-item>

              <v-card-text class="pt-4">
                 <div v-if="selectedSeats.length === 0" class="text-center py-8 text-grey">
                    <v-icon size="40" class="mb-2">mdi-cursor-default-click-outline</v-icon>
                    <p>Selecciona tus asientos en el mapa</p>
                 </div>

                 <v-list v-else lines="two">
                    <v-list-item
                       v-for="seat in selectedSeats"
                       :key="seat.seat_id"
                       :title="`Asiento ${seat.number}`"
                       :subtitle="seat.type"
                    >
                       <template v-slot:append>
                          <span class="font-weight-bold">${{ seat.price }}</span>
                          <v-btn 
                             icon="mdi-close" 
                             size="x-small" 
                             variant="text" 
                             color="red"
                             class="ml-2"
                             @click="toggleSeat(seat)"
                          ></v-btn>
                       </template>
                    </v-list-item>
                    
                    <v-divider class="my-2"></v-divider>
                    
                    <div class="d-flex justify-space-between text-h6 font-weight-bold mt-4">
                       <span>Total a pagar</span>
                       <span>${{ totalPrice }}</span>
                    </div>
                 </v-list>
              </v-card-text>

              <v-card-actions class="pa-4">
                 <v-btn 
                    block 
                    color="success" 
                    size="x-large" 
                    variant="flat"
                    :disabled="selectedSeats.length === 0"
                    @click="handleCheckout"
                 >
                    Confirmar Reserva
                 </v-btn>
              </v-card-actions>
           </v-card>
        </v-col>
      </v-row>

    </v-container>
  </MainLayout>
</template>

<style scoped>
/* 
   GRID MAGICO:
   Simula la distribución 2 pasillo 2. 
   Repite columnas de 1 fracción, 1 fracción, pasillo, 1 fracción, 1 fracción.
*/
.bus-grid-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr) 40px repeat(2, 1fr); 
    gap: 12px 8px; /* Gap vertical, Gap horizontal */
    max-width: 350px; /* Ancho realista de bus */
    margin: 0 auto;
}

/* Asientos que caen en el pasillo (hack visual si fuera necesario) */
.seat-wrapper {
    position: relative;
}

.seat-btn {
    position: relative;
    border-radius: 8px; /* Forma cuadrada redondeada */
}

.seat-number {
    position: absolute;
    bottom: -2px;
    right: -2px;
    background-color: rgba(0,0,0,0.7);
    color: white;
    font-size: 10px;
    padding: 2px 4px;
    border-radius: 4px;
    line-height: 1;
}
</style>