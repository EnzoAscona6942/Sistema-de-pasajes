<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'; // Usamos el Layout principal
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

// Control para mostrar/ocultar contraseña
const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <MainLayout>
        <Head title="Iniciar Sesión" />

        <v-container class="fill-height py-12" fluid>
            <v-row align="center" justify="center">
                <v-col cols="12" sm="8" md="6" lg="4">
                    
                    <div class="text-center mb-6">
                        <v-icon size="64" color="indigo-darken-4">mdi-bus-stop</v-icon>
                        <h2 class="text-h4 font-weight-bold text-indigo-darken-4 mt-2">Bienvenido</h2>
                        <p class="text-grey-darken-1">Ingresa a tu cuenta para continuar</p>
                    </div>

                    <v-card elevation="4" class="rounded-lg pa-4">
                        <v-alert
                            v-if="status"
                            type="success"
                            variant="tonal"
                            class="mb-4"
                        >
                            {{ status }}
                        </v-alert>

                        <v-form @submit.prevent="submit">
                            
                            <!-- Email -->
                            <v-text-field
                                v-model="form.email"
                                label="Correo Electrónico"
                                type="email"
                                variant="outlined"
                                density="comfortable"
                                color="indigo"
                                prepend-inner-icon="mdi-email-outline"
                                :error-messages="form.errors.email"
                                class="mb-2"
                            ></v-text-field>

                            <!-- Contraseña -->
                            <v-text-field
                                v-model="form.password"
                                label="Contraseña"
                                :type="showPassword ? 'text' : 'password'"
                                variant="outlined"
                                density="comfortable"
                                color="indigo"
                                prepend-inner-icon="mdi-lock-outline"
                                :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                                @click:append-inner="showPassword = !showPassword"
                                :error-messages="form.errors.password"
                                class="mb-1"
                            ></v-text-field>

                            <!-- Remember Me & Forgot Password -->
                            <div class="d-flex align-center justify-space-between mb-4">
                                <v-checkbox
                                    v-model="form.remember"
                                    label="Recordarme"
                                    color="indigo"
                                    density="compact"
                                    hide-details
                                ></v-checkbox>

                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-caption text-decoration-none text-indigo font-weight-bold"
                                >
                                    ¿Olvidaste tu contraseña?
                                </Link>
                            </div>

                            <!-- Botón Submit -->
                            <v-btn
                                block
                                color="indigo-darken-4"
                                size="large"
                                type="submit"
                                :loading="form.processing"
                                class="text-capitalize font-weight-bold"
                            >
                                Iniciar Sesión
                            </v-btn>

                            <!-- Link a Registro -->
                            <div class="text-center mt-6">
                                <p class="text-body-2 text-grey-darken-1">
                                    ¿No tienes una cuenta?
                                    <Link :href="route('register')" class="text-indigo font-weight-bold text-decoration-none">
                                        Regístrate aquí
                                    </Link>
                                </p>
                            </div>

                        </v-form>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </MainLayout>
</template>