<?php

namespace App\DTOs\Session;

class SessionCreateDTO
{
    public string $room;
    public string $session_start;
    public string $session_end;

    public function __construct(string $room, string $session_start, string $session_end)
    {
        $this->room = $room;
        $this->session_start = $session_start;
        $this->session_end = $session_end;
    }
}
