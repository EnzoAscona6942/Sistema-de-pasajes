import { reactive } from 'vue';

// Estado reactivo global (Singleton)
// Se mantiene activo mientras navegues con Inertia
const globalState = reactive({
    snackbar: {
        show: false,
        text: '',
        color: 'info',
        icon: 'mdi-information',
        timeout: 3000
    },
    dialog: {
        show: false,
        title: '',
        text: '',
        color: 'primary',
        onConfirm: null // Callback para la acción
    }
});

export function useNotification() {
    
    // 1. Notificación flotante (Toast)
    const notify = (text, type = 'success') => {
        let color = 'success';
        let icon = 'mdi-check-circle';

        switch (type) {
            case 'error': color = 'error'; icon = 'mdi-alert-circle'; break;
            case 'warning': color = 'warning'; icon = 'mdi-alert'; break;
            case 'info': color = 'info'; icon = 'mdi-information'; break;
        }

        globalState.snackbar.text = text;
        globalState.snackbar.color = color;
        globalState.snackbar.icon = icon;
        globalState.snackbar.show = true;
    };

    // 2. Diálogo de confirmación o error crítico
    const confirmDialog = (title, text, onConfirmAction, type = 'warning') => {
        globalState.dialog.title = title;
        globalState.dialog.text = text;
        globalState.dialog.color = type === 'error' ? 'red-darken-1' : 'primary';
        globalState.dialog.onConfirm = onConfirmAction;
        globalState.dialog.show = true;
    };

    const closeDialog = () => {
        globalState.dialog.show = false;
    };

    return { 
        globalState, 
        notify, 
        confirmDialog,
        closeDialog
    };
}