<x-mail::message>
# ¡Todo listo para tu viaje! 🚌

Hola **{{ $booking->user->name }}**, tu pago ha sido procesado y tu asiento está asegurado.

Aquí tienes tu pasaje digital. Puedes mostrar este correo al chofer al momento de subir.

<x-mail::panel>
## 🎫 DETALLES DEL PASAJE
**Código de Reserva:** {{ $booking->id }}

**Empresa:** {{ $booking->trip->bus->company->name }}  
**Servicio:** {{ $booking->trip->bus->service_type->label() }}

---

### 📍 ITINERARIO
**Origen:** {{ $booking->trip->origin->name }} ({{ $booking->trip->origin->city }})  
**Destino:** {{ $booking->trip->destination->name }} ({{ $booking->trip->destination->city }})

**Fecha de Salida:** {{ $booking->trip->departure_time->format('d/m/Y') }}  
**Hora:** {{ $booking->trip->departure_time->format('H:i') }} hs  
**Andén Estimado:** {{ rand(1, 20) }}

---

### 💺 TU LUGAR
**Asiento:** #{{ $booking->seat->seat_number }}  
**Piso:** {{ $booking->seat->floor }}  
**Precio Pagado:** ${{ number_format($booking->price_paid, 2) }}
</x-mail::panel>

<div style="text-align: center; margin-top: 20px;">
    <p>Escanea este código al subir:</p>
    <img src="{{ $qrCodeUrl }}" alt="Código QR del Pasaje" style="border: 2px solid #ddd; padding: 5px; border-radius: 5px;">
</div>

<x-mail::button :url="url('/dashboard')">
Ver en Mis Viajes
</x-mail::button>

¡Buen viaje te desea el equipo de,<br>
{{ config('app.name') }}
</x-mail::message>