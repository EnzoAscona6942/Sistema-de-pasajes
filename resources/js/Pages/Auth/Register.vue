<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    dni: '', // Campo importante que agregamos antes
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <MainLayout>
        <Head title="Crear Cuenta" />

        <v-container class="fill-height py-12">
            <v-row align="center" justify="center">
                <v-col cols="12" sm="8" md="6" lg="5">
                    
                    <div class="text-center mb-6">
                        <h2 class="text-h4 font-weight-bold text-indigo-darken-4">Crear Cuenta</h2>
                        <p class="text-grey-darken-1">Únete para comprar tus pasajes más rápido</p>
                    </div>

                    <v-card elevation="4" class="rounded-lg pa-4">
                        <v-form @submit.prevent="submit">
                            
                            <!-- Nombre -->
                            <v-text-field
                                v-model="form.name"
                                label="Nombre Completo"
                                variant="outlined"
                                density="comfortable"
                                color="indigo"
                                prepend-inner-icon="mdi-account-outline"
                                :error-messages="form.errors.name"
                                class="mb-2"
                            ></v-text-field>

                            <!-- DNI -->
                            <v-text-field
                                v-model="form.dni"
                                label="DNI / Documento"
                                type="number"
                                variant="outlined"
                                density="comfortable"
                                color="indigo"
                                prepend-inner-icon="mdi-card-account-details-outline"
                                :error-messages="form.errors.dni"
                                class="mb-2"
                            ></v-text-field>

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

                            <v-row dense>
                                <v-col cols="12" md="6">
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
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <!-- Confirmar -->
                                    <v-text-field
                                        v-model="form.password_confirmation"
                                        label="Confirmar"
                                        :type="showPassword ? 'text' : 'password'"
                                        variant="outlined"
                                        density="comfortable"
                                        color="indigo"
                                        prepend-inner-icon="mdi-lock-check-outline"
                                        :error-messages="form.errors.password_confirmation"
                                    ></v-text-field>
                                </v-col>
                            </v-row>

                            <!-- Botón Registrar -->
                            <v-btn
                                block
                                color="indigo-darken-4"
                                size="large"
                                type="submit"
                                :loading="form.processing"
                                class="text-capitalize font-weight-bold mt-4"
                            >
                                Registrarse
                            </v-btn>

                            <!-- Link a Login -->
                            <div class="text-center mt-6">
                                <p class="text-body-2 text-grey-darken-1">
                                    ¿Ya tienes una cuenta?
                                    <Link :href="route('login')" class="text-indigo font-weight-bold text-decoration-none">
                                        Ingresa aquí
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