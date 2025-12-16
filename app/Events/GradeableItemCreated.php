<?php

namespace App\Events;

use App\Models\GradeableItem;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GradeableItemCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public GradeableItem $gradeableItem;

    /**
     * Create a new event instance.
     */
    public function __construct(GradeableItem $gradeableItem)
    {
        $this->gradeableItem = $gradeableItem;
    }
}
