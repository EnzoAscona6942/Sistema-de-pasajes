<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Mail\TicketConfirmed;
use Illuminate\Bus\Queueable; // Traits necesarios
use Illuminate\Contracts\Queue\ShouldQueue; // Interfaz clave
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTicketJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Booking $booking;

    /**
     * Create a new job instance.
     */
    public function __construct(Booking $booking)
    {
        // Cargamos relaciones para evitar N+1 querys dentro del mail
        // aunque SerializesModels recarga el modelo, es buena práctica saber qué necesitas.
        $this->booking = $booking;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Enviamos el correo al usuario dueño de la reserva
        Mail::to($this->booking->user->email)
            ->send(new TicketConfirmed($this->booking));
    }
}