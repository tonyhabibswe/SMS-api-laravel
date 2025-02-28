<?php

namespace App\DTOs\Session;

class SessionCreateDTO
{
    public string $room;
    public string $sessionStart;
    public string $sessionEnd;

    public function __construct(string $room, string $sessionStart, string $sessionEnd)
    {
        $this->room = $room;
        $this->sessionStart = $sessionStart;
        $this->sessionEnd = $sessionEnd;
    }
}
