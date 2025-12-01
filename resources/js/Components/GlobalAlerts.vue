<script setup>
import { useNotification } from '@/Composables/useNotification';

const { globalState, closeDialog } = useNotification();

const handleConfirm = () => {
    if (globalState.dialog.onConfirm) {
        globalState.dialog.onConfirm();
    }
    closeDialog();
};
</script>

<template>
    <!-- 1. SNACKBAR (Toast) -->
    <v-snackbar
        v-model="globalState.snackbar.show"
        :color="globalState.snackbar.color"
        :timeout="globalState.snackbar.timeout"
        location="top right"
        variant="elevated"
        elevation="8"
    >
        <div class="d-flex align-center">
            <v-icon :icon="globalState.snackbar.icon" class="mr-2" />
            <span class="font-weight-bold">{{ globalState.snackbar.text }}</span>
        </div>

        <template v-slot:actions>
            <v-btn icon="mdi-close" variant="text" @click="globalState.snackbar.show = false"></v-btn>
        </template>
    </v-snackbar>

    <!-- 2. DIÁLOGO (Modal) -->
    <v-dialog v-model="globalState.dialog.show" max-width="400" persistent>
        <v-card>
            <v-toolbar :color="globalState.dialog.color" density="compact">
                <v-toolbar-title class="text-white font-weight-bold">
                    {{ globalState.dialog.title }}
                </v-toolbar-title>
                <v-btn icon="mdi-close" variant="text" color="white" @click="closeDialog"></v-btn>
            </v-toolbar>
            
            <v-card-text class="pa-4 text-body-1">
                {{ globalState.dialog.text }}
            </v-card-text>

            <v-card-actions class="justify-end pa-4">
                <v-btn variant="text" @click="closeDialog">Cancelar</v-btn>
                <v-btn 
                    :color="globalState.dialog.color" 
                    variant="elevated" 
                    @click="handleConfirm"
                >
                    Aceptar
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>