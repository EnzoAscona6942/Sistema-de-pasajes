<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Doughnut } from 'vue-chartjs'; // Gráfico tipo Dona (más moderno que Torta)

// Registrar componentes de ChartJS
ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    bookings: Array,
    chartData: Object
});

// Configuración del Gráfico
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom' }
    }
};

// Helper para colores de estado
const getStatusColor = (status) => {
    switch(status) {
        case 'confirmed': return 'success';
        case 'cancelled': return 'error';
        case 'pending': return 'warning';
        default: return 'grey';
    }
};
</script>

<template>
    <Head title="Mis Pasajes" />

    <MainLayout>
        <v-container class="py-8">
            
            <h1 class="text-h4 font-weight-bold text-indigo-darken-4 mb-6">
                <v-icon start>mdi-view-dashboard</v-icon> Mi Panel
            </h1>

            <v-row>
                <!-- COLUMNA IZQUIERDA: HISTORIAL DE PASAJES -->
                <v-col cols="12" md="8">
                    <v-card elevation="2" class="rounded-lg">
                        <v-card-title class="d-flex align-center py-4 px-4 bg-grey-lighten-4">
                            <v-icon color="indigo" class="mr-2">mdi-ticket-account</v-icon>
                            Historial de Viajes
                        </v-card-title>
                        
                        <v-divider></v-divider>

                        <div v-if="bookings.length === 0" class="text-center py-12">
                            <v-icon size="64" color="grey-lighten-2">mdi-bus-stop</v-icon>
                            <p class="text-grey text-body-1 mt-2">Aún no has realizado viajes.</p>
                            <Link href="/" as="div" class="mt-4">
                                <v-btn color="indigo" variant="flat">Buscar Pasajes</v-btn>
                            </Link>
                        </div>

                        <v-list v-else lines="three">
                            <v-list-item v-for="booking in bookings" :key="booking.id" class="py-3">
                                <!-- Icono Estado -->
                                <template v-slot:prepend>
                                    <v-avatar :color="getStatusColor(booking.status)" variant="tonal" class="mr-3">
                                        <v-icon>mdi-bus</v-icon>
                                    </v-avatar>
                                </template>

                                <!-- Detalles -->
                                <v-list-item-title class="font-weight-bold text-h6">
                                    {{ booking.destination }}
                                </v-list-item-title>
                                <v-list-item-subtitle class="mt-1 opacity-100">
                                    <span class="text-indigo font-weight-bold">Salida:</span> {{ booking.date }} <br>
                                    <span class="text-grey-darken-2">{{ booking.origin }}</span>
                                </v-list-item-subtitle>

                                <!-- Precio y Acciones -->
                                <template v-slot:append>
                                    <div class="text-right">
                                        <div class="text-h6 font-weight-black text-green-darken-2">
                                            ${{ booking.price }}
                                        </div>
                                        <v-chip size="x-small" :color="getStatusColor(booking.status)" class="text-uppercase font-weight-bold mb-2">
                                            {{ booking.status }}
                                        </v-chip>
                                        <br>
                                        <!-- Botón para ver QR (Dialog simple) -->
                                        <v-dialog width="300">
                                            <template v-slot:activator="{ props }">
                                                <v-btn v-bind="props" size="small" variant="text" color="indigo" prepend-icon="mdi-qrcode">
                                                    Ver Ticket
                                                </v-btn>
                                            </template>
                                            <v-card>
                                                <v-img :src="booking.qr_code" height="300" cover></v-img>
                                                <v-card-actions>
                                                    <v-btn color="primary" block @click="isActive = false">Cerrar</v-btn>
                                                </v-card-actions>
                                            </v-card>
                                        </v-dialog>
                                    </div>
                                </template>
                                <v-divider class="mt-3"></v-divider>
                            </v-list-item>
                        </v-list>
                    </v-card>
                </v-col>

                <!-- COLUMNA DERECHA: ESTADÍSTICAS -->
                <v-col cols="12" md="4">
                    <!-- Tarjeta Resumen -->
                    <v-card elevation="2" class="rounded-lg mb-4 bg-indigo-darken-3 text-white">
                        <v-card-text class="d-flex align-center">
                            <div>
                                <div class="text-overline">Total Viajes</div>
                                <div class="text-h3 font-weight-black">{{ bookings.length }}</div>
                            </div>
                            <v-spacer></v-spacer>
                            <v-icon size="64" color="white" class="opacity-20">mdi-trophy-award</v-icon>
                        </v-card-text>
                    </v-card>

                    <!-- Tarjeta Gráfico -->
                    <v-card elevation="2" class="rounded-lg pa-4" style="min-height: 350px;">
                        <v-card-title class="text-subtitle-1 font-weight-bold text-center mb-4">
                            Tus Destinos (Últimos 30 días)
                        </v-card-title>
                        
                        <div v-if="chartData.datasets[0].data.length > 0" style="height: 250px;">
                            <Doughnut :data="chartData" :options="chartOptions" />
                        </div>
                        
                        <div v-else class="fill-height d-flex align-center justify-center flex-column text-grey text-center py-8">
                            <v-icon size="48" class="mb-2">mdi-chart-pie</v-icon>
                            <p>No hay datos recientes<br>para graficar.</p>
                        </div>
                    </v-card>
                </v-col>
            </v-row>

        </v-container>
    </MainLayout>
</template>