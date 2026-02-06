<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductionAnomalyDetected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mineArea;
    public $anomalyType;
    public $severity;
    public $data;
    public $teamId;

    public function __construct($mineArea, $anomalyType, $severity, $data, $teamId)
    {
        $this->mineArea = $mineArea;
        $this->anomalyType = $anomalyType;
        $this->severity = $severity;
        $this->data = $data;
        $this->teamId = $teamId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel("team.{$this->teamId}.operations");
    }

    public function broadcastAs()
    {
        return 'production.anomaly';
    }

    public function broadcastWith()
    {
        return [
            'mine_area_id' => $this->mineArea->id,
            'mine_area_name' => $this->mineArea->name,
            'anomaly_type' => $this->anomalyType,
            'severity' => $this->severity,
            'data' => $this->data,
            'timestamp' => now()->toIso8601String(),
            'requires_attention' => $this->severity === 'critical' || $this->severity === 'high',
        ];
    }
}
