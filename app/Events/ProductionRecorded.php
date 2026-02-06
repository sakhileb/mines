<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\MineArea;
use App\Models\MineAreaProduction;

class ProductionRecorded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $production;
    public $mineArea;

    public function __construct(MineAreaProduction $production)
    {
        $this->production = $production;
        $this->mineArea = $production->mineArea;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('mine-area.' . $this->mineArea->team_id);
    }

    public function broadcastAs()
    {
        return 'production.recorded';
    }

    public function broadcastWith()
    {
        return [
            'mine_area_id' => $this->mineArea->id,
            'date' => $this->production->date,
            'material_name' => $this->production->material_name,
            'material_tonnage' => $this->production->material_tonnage,
            'timestamp' => now(),
        ];
    }
}
