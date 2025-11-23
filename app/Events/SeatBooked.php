<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // "Now" para inmediatez sin colas
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeatBooked implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tripId;
    public $seatId;

    public function __construct($tripId, $seatId)
    {
        $this->tripId = $tripId;
        $this->seatId = $seatId;
    }

    public function broadcastOn(): array
    {
        // Usamos un canal público para que cualquiera viendo el viaje reciba la data
        return [
            new Channel('trips.' . $this->tripId),
        ];
    }
    
    public function broadcastAs(): string
    {
        return 'seat.booked';
    }
}