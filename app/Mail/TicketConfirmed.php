<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // No lo usamos aquí, lo usamos en el Job
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;

    /**
     * Recibimos la reserva completa
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu viaje está confirmado! - Pasaje #' . $this->booking->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tickets.confirmed',
            with: [
                'qrCodeUrl' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . $this->booking->id
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}