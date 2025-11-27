<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // <--- Importante: NOW
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeatSelecting implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tripId;
    public $seatId;
    public $isSelecting; // true = seleccionó, false = deseleccionó

    public function __construct($tripId, $seatId, $isSelecting)
    {
        $this->tripId = $tripId;
        $this->seatId = $seatId;
        $this->isSelecting = $isSelecting;
    }

    public function broadcastOn(): array
    {
        // Usamos el mismo canal público del viaje
        return [
            new Channel('trips.' . $this->tripId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'seat.selecting';
    }

    public function broadcastWith(): array
    {
        return [
            'seat_id' => $this->seatId,
            'is_selecting' => $this->isSelecting
        ];
    }
}