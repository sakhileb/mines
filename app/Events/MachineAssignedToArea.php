<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\MineArea;

class MachineAssignedToArea implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mineArea;
    public $machine;
    public $timestamp;

    public function __construct(MineArea $mineArea, $machine)
    {
        $this->mineArea = $mineArea;
        $this->machine = $machine;
        $this->timestamp = now();
    }

    public function broadcastOn()
    {
        return new PrivateChannel('mine-area.' . $this->mineArea->team_id);
    }

    public function broadcastAs()
    {
        return 'machine.assigned';
    }

    public function broadcastWith()
    {
        return [
            'mine_area_id' => $this->mineArea->id,
            'mine_area_name' => $this->mineArea->name,
            'machine_id' => $this->machine->id,
            'machine_name' => $this->machine->name,
            'timestamp' => $this->timestamp,
        ];
    }
}
