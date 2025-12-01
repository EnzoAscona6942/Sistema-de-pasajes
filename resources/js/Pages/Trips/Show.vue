<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
// import axios from 'axios';
import { useNotification } from '@/Composables/useNotification';

const props = defineProps({
  tripId: String,
  passengers: Number
});
const { notify, confirmDialog } = useNotification();
const seatsBeingViewed = ref([]);
const trip = ref(null);
const seatsByFloor = ref({});
const loading = ref(true);
const selectedSeats = ref([]);
const page = usePage();
const form = useForm({
  trip_id: props.tripId,
  bookings: [] 
});

onMounted(async () => {
     try {
        console.log("1. Iniciando petición API...");
        const response = await axios.get(`/api/v1/trips/${props.tripId}/seats`);
        
        console.log("2. Respuesta API recibida:", response.data);

        // ASIGNACIÓN SEGURA: Si viene null/undefined, usamos un objeto vacío
        trip.value = response.data.trip_info || {};
        seatsByFloor.value = response.data.seats_by_floor || {};
        
        initWebSockets();
    } catch (e) {
        console.error("ERROR FATAL EN CARGA:", e);
        alert("Error cargando los asientos. Revisa la consola (F12).");
    } finally {
        console.log("3. Finalizando carga...");
        loading.value = false; // Esto debería quitar el spinner
    }
});

onUnmounted(() => {
    // Importante: Desconectar al salir para no escuchar eventos de otros viajes
    if(window.Echo) {
        window.Echo.leave(`trips.${props.tripId}`);
    }
});

const initWebSockets = () => {
    if(window.Echo) {
        const channel = window.Echo.channel(`trips.${props.tripId}`);
        
        // Escuchar RESERVA FIRME (Gris)
        channel.listen('.seat.booked', (e) => {
             updateSeatStatus(e.seat_id, 'occupied');
             // Si estaba en "viewed", lo quitamos
             removeSeatFromViewed(e.seat_id);
        });

        // Escuchar SELECCIÓN TEMPORAL (Naranja)
        channel.listen('.seat.selecting', (e) => {
            // Ignorar si soy yo mismo quien disparó el evento (Truco simple: si ya lo tengo seleccionado yo, ignoro)
            // Nota: Para hacerlo perfecto necesitarías un socketId, pero esto funciona al 99%
            const isSelectedByMe = selectedSeats.value.find(s => s.seat_id === e.seat_id);
            
            if (!isSelectedByMe) {
                if (e.is_selecting) {
                    if (!seatsBeingViewed.value.includes(e.seat_id)) {
                        seatsBeingViewed.value.push(e.seat_id);
                        showToast(`Alguien seleccionó el asiento ${e.seat_id}`); // Opcional
                    }
                } else {
                    removeSeatFromViewed(e.seat_id);
                }
            }
        });
    }
};

const removeSeatFromViewed = (seatId) => {
    seatsBeingViewed.value = seatsBeingViewed.value.filter(id => id !== seatId);
};

// Función auxiliar para actualizar la UI sin recargar
const updateSeatStatusFromSocket = (seatId) => {
    // Recorremos los pisos (seatsByFloor es un objeto: { '1': [...], '2': [...] })
    for (const floor in seatsByFloor.value) {
        const seats = seatsByFloor.value[floor];
        const seat = seats.find(s => s.seat_id === seatId);
        
        if (seat) {
            seat.status = 'occupied'; // Esto cambiará el color a gris automáticamente
            
            // Si yo tenía seleccionado ese mismo asiento, me lo quita y me avisa
            const mySelectionIndex = selectedSeats.value.findIndex(s => s.seat_id === seatId);
            if (mySelectionIndex !== -1) {
                selectedSeats.value.splice(mySelectionIndex, 1);
            notify('¡Alguien más acaba de reservar el asiento que mirabas!', 'warning');            }
        }
    }
};

const toggleSeat = async (seat) => {
  if (seat.status === 'occupied') return;
  
  // Si alguien más lo está mirando, mostramos advertencia (opcional)
  if (seatsBeingViewed.value.includes(seat.seat_id)) {
      // Puedes bloquear la selección o solo avisar. Aquí solo avisamos.
      console.log("Aviso: Alguien más está mirando este asiento.");
  }

  const index = selectedSeats.value.findIndex(s => s.seat_id === seat.seat_id);
  let action = '';

  if (index === -1) {
    // Lógica de límite de pasajeros...
    if (selectedSeats.value.length >= props.passengers) {
      notify(`Solo solicitaste ${props.passengers} pasajes. Deselecciona uno para cambiar.`, 'error');
      return;
    }
    selectedSeats.value.push(seat);
    action = 'select';
  } else {
    selectedSeats.value.splice(index, 1);
    action = 'deselect';
  }

  // AVISAR A LA API (Fuego y olvido, no esperamos respuesta para no trabar la UI)
  window.axios.post(`/api/v1/trips/${props.tripId}/seat-selection`, {
      seat_id: seat.seat_id,
      action: action
  });
};
const totalPrice = computed(() => selectedSeats.value.reduce((sum, s) => sum + parseFloat(s.price), 0));

const handleCheckout = async () => {
    // 1. Validar cantidad
    if (selectedSeats.value.length !== props.passengers) {
        notify(`Por favor selecciona exactamente ${props.passengers} asientos.`, 'warning');
        return;
    }

    // 2. Validar Autenticación
    if (!page.props.auth.user) {
        // Guardamos la URL actual en la sesión para volver después del login
        // Laravel Breeze maneja "intended" automáticamente si usamos router.visit
        confirmDialog(
            'Iniciar Sesión', 
            'Debes estar registrado para comprar pasajes. ¿Deseas ingresar ahora?',
            () => {
                router.visit(route('login'), {
                    data: { return_url: window.location.href }
                });
            },
            'info'
        );
        return;
    }

    // 3. Enviar datos (Ahora a la ruta WEB, que comparte sesión)
    try {
        // Usamos window.axios que ya tiene el token CSRF y cookies
        const response = await window.axios.post('/bookings', {
            trip_id: props.tripId,
            // Enviamos array de asientos si quieres soportar múltiples, 
            // o modificas tu controller para recibir array.
            // Por ahora, para mantener compatibilidad con tu controller actual,
            // enviaremos uno por uno o el controller debe adaptarse.
            // Asumamos que adaptaste el controller o enviamos el primero por ahora:
            seat_id: selectedSeats.value[0].seat_id 
        });

        if (response.status === 201) {
            notify('¡Reserva Exitosa! Redirigiendo...', 'success');
            router.visit('/');
        }
    } catch (error) {
         console.error(error);
        const msg = error.response?.data?.message || 'Error al reservar';
        // REEMPLAZO DE ALERT:
        notify(msg, 'error');
        
        if (error.response?.status === 401) window.location.href = '/login';
    }
};

// Estilos dinámicos para el asiento
const getSeatColor = (seat) => {
  if (seat.status === 'occupied') return 'grey-lighten-3'; // Ocupado Real (Gris)
  
  // Si YO lo tengo seleccionado (Verde)
  if (selectedSeats.value.find(s => s.seat_id === seat.seat_id)) return 'success';
  
  // Si OTRO lo está mirando (Naranja/Alerta)
  if (seatsBeingViewed.value.includes(seat.seat_id)) return 'warning';

  return 'white'; // Libre
};

const getSeatIconColor = (seat) => {
    if (seat.status === 'occupied') return 'grey-lighten-1';
    if (selectedSeats.value.find(s => s.seat_id === seat.seat_id)) return 'white';
    if (seatsBeingViewed.value.includes(seat.seat_id)) return 'white'; // Icono blanco sobre fondo naranja
    return 'indigo-darken-2';
};
const showSnackbar = ref(false);
const snackbarText = ref('');
const showToast = (msg) => {
    snackbarText.value = msg;
    showSnackbar.value = true;
};
</script>

<template>
  <MainLayout>
    <v-container class="py-8">
        
        <!-- Loading -->
        <div v-if="loading" class="d-flex justify-center align-center" style="height: 400px;">
            <v-progress-circular indeterminate color="indigo" size="64"></v-progress-circular>
        </div>

        <v-row v-else>
            <!-- COLUMNA IZQUIERDA: VISTA DE COLECTIVOS -->
            <v-col cols="12" lg="8">
                <v-card class="pa-6 bg-grey-lighten-4 rounded-xl" elevation="2">
                    
                    <!-- Cabecera del Viaje -->
                    <div class="mb-6 border-b pb-4">
                        <h2 class="text-h5 font-weight-bold text-indigo-darken-4">
                            {{ trip?.origin }} <v-icon color="grey">mdi-arrow-right</v-icon> {{ trip?.destination }}
                        </h2>
                        <div class="text-subtitle-2 text-grey-darken-1 mt-1">
                            Bus: {{ trip?.bus_model }} | Salida: {{ trip?.date }}
                        </div>
                    </div>

                    <!-- CONTENEDOR PARALELO DE PISOS -->
                    <v-row>
                        <!-- Iteramos sobre los pisos. 
                             Si hay 2 pisos, md="6" los pone lado a lado. 
                             Si hay 1, md="12" lo centra. -->
                        <v-col v-for="(seats, floor) in seatsByFloor" :key="floor" cols="12" :md="(seatsByFloor && Object.keys(seatsByFloor).length > 1) ? 6 : 12">
                            <div class="bus-deck-container bg-white rounded-xl py-6 px-4 elevation-3 border">
                                
                                <!-- Cabecera del Piso -->
                                <div class="d-flex justify-space-between align-center mb-4 px-2">
                                    <div class="d-flex align-center text-indigo font-weight-bold">
                                        <v-icon start>mdi-stairs</v-icon> Piso {{ floor }}
                                    </div>
                                    <v-chip size="x-small" color="grey-lighten-2">Frente <v-icon end size="small">mdi-steering</v-icon></v-chip>
                                </div>

                                <!-- GRILLA DE ASIENTOS -->
                                <div class="seats-grid">
                                    <div v-for="seat in seats" :key="seat.seat_id" class="seat-wrapper">
                                        <v-tooltip location="top" open-delay="200">
    <template v-slot:activator="{ props }">
        <v-btn 
            v-bind="props"
            :color="getSeatColor(seat)"
            class="seat-btn rounded-lg mb-2"
            :class="{'occupied-seat': seat.status === 'occupied'}"
            width="50" 
            height="50"
            :disabled="seat.status === 'occupied'"
            @click="toggleSeat(seat)"
            elevation="1"
            border
        >
            <!-- Icono y número... -->
             <div class="d-flex flex-column align-center">
                <v-icon size="22" :color="getSeatIconColor(seat)">
                    <!-- Cambiamos icono si está siendo mirado -->
                    {{ seatsBeingViewed.includes(seat.seat_id) ? 'mdi-account-eye' : 'mdi-seat-recline-extra' }}
                </v-icon>
                <span class="seat-number">{{ seat.number }}</span>
            </div>
        </v-btn>
    </template>
    <v-snackbar v-model="showSnackbar" color="orange-darken-2" timeout="2000" location="top right">
        {{ snackbarText }}
    </v-snackbar>
    <!-- CONTENIDO DEL TOOLTIP -->
    <div class="text-center">
        <div v-if="seat.status === 'occupied'" class="text-red font-weight-bold">OCUPADO</div>
        <div v-else-if="seatsBeingViewed.includes(seat.seat_id)" class="text-warning font-weight-bold">
            <v-icon size="small">mdi-alert</v-icon> ¡Alguien lo está mirando!
        </div>
        <div v-else>
            <div class="font-weight-bold">Asiento {{ seat.number }}</div>
            <div>{{ seat.type }}</div>
            <div class="text-success font-weight-bold">${{ seat.price }}</div>
        </div>
    </div>
</v-tooltip>
                                    </div>
                                </div>
                                
                                <!-- Decoración trasera -->
                                <div class="mt-4 d-flex justify-center">
                                    <div class="bg-grey-lighten-4 rounded w-50" style="height: 8px;"></div>
                                </div>

                            </div>
                        </v-col>
                    </v-row>

                    <!-- Referencias -->
                    <div class="d-flex justify-center gap-4 mt-6 text-caption text-grey-darken-2">
                        <div class="d-flex align-center"><v-sheet color="white" width="16" height="16" class="border mr-2 rounded"></v-sheet> Libre</div>
                        <div class="d-flex align-center"><v-sheet color="success" width="16" height="16" class="mr-2 rounded"></v-sheet> Tu Selección</div>
                        <div class="d-flex align-center"><v-sheet color="grey-lighten-3" width="16" height="16" class="border mr-2 rounded"></v-sheet> Ocupado</div>
                    </div>

                </v-card>
            </v-col>

            <!-- COLUMNA DERECHA: RESUMEN (STICKY) -->
            <v-col cols="12" lg="4">
                <v-card class="rounded-xl elevation-3 position-sticky" style="top: 20px;">
                    <v-card-item class="bg-indigo-darken-3 text-white py-4">
                        <v-card-title class="text-h6">Tu Selección</v-card-title>
                    </v-card-item>

                    <v-card-text class="pa-0">
                        <v-list v-if="selectedSeats.length > 0" lines="two" class="py-2">
                            <v-list-item v-for="s in selectedSeats" :key="s.seat_id">
                                <template v-slot:prepend>
                                    <v-avatar color="indigo-lighten-5" class="text-indigo font-weight-bold">
                                        {{ s.number }}
                                    </v-avatar>
                                </template>
                                <v-list-item-title class="font-weight-bold">Asiento {{ s.number }} (Piso {{ s.floor }})</v-list-item-title>
                                <v-list-item-subtitle>{{ s.type }}</v-list-item-subtitle>
                                <template v-slot:append>
                                    <div class="text-h6 text-grey-darken-3">${{ s.price }}</div>
                                    <v-btn icon="mdi-close" variant="text" size="small" color="red" class="ml-2" @click="toggleSeat(s)"></v-btn>
                                </template>
                            </v-list-item>
                        </v-list>
                        
                        <div v-else class="text-center py-12 px-4">
                            <v-icon size="48" color="grey-lighten-2" class="mb-3">mdi-sofa-single-outline</v-icon>
                            <p class="text-grey">Selecciona un asiento en el mapa para continuar.</p>
                        </div>
                    </v-card-text>

                    <v-divider></v-divider>

                    <div class="pa-4 bg-grey-lighten-5">
                        <div class="d-flex justify-space-between text-h6 font-weight-bold mb-4 text-indigo-darken-4">
                            <span>Total</span>
                            <span>${{ totalPrice }}</span>
                        </div>
                        <v-btn 
                            block 
                            color="success" 
                            size="x-large" 
                            class="text-capitalize rounded-lg font-weight-bold"
                            elevation="2"
                            :disabled="selectedSeats.length === 0"
                            @click="handleCheckout"
                        >
                            Confirmar Reserva
                        </v-btn>
                    </div>
                </v-card>
            </v-col>
        </v-row>

    </v-container>
  </MainLayout>
</template>

<style scoped>
.seats-grid {
    display: grid;
    /* CAMBIO: Usamos 4 columnas exactas */
    grid-template-columns: repeat(4, 1fr);
    gap: 10px; /* Espaciado general */
    justify-content: center;
    max-width: 260px; /* Ajustamos el ancho para que se vea compacto */
    margin: 0 auto;
}

.seat-wrapper {
    display: flex;
    justify-content: center;
}

/* TRUCO DEL PASILLO: */
/* A cada 2do elemento de cada fila (4n + 2), le damos margen a la derecha */
.seat-wrapper:nth-child(4n + 2) {
    margin-right: 25px; /* Esto crea el pasillo visualmente */
}

.seat-btn {
    transition: all 0.2s;
    border: 1px solid #e0e0e0 !important;
    position: relative; /* Para posicionar el número */
}

.seat-number {
    font-size: 10px;
    /* Ajuste fino para que el número no empuje el icono */
    position: absolute; 
    bottom: 2px;
    right: 4px;
    color: #666;
    font-weight: bold;
}

.occupied-seat {
    opacity: 0.6;
    background-color: #EEEEEE !important; /* Gris claro visual */
    cursor: not-allowed;
}
</style>