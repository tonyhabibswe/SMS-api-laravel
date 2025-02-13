<?php

namespace App\DTOs\Session;

class SessionListDTO
{
    public int $id;
    public string $room;
    public string $sessionStart;
    public string $sessionEnd;

    public function __construct(int $id, string $room, string $sessionStart, string $sessionEnd)
    {
        $this->id = $id;
        $this->room = $room;
        $this->sessionStart = $sessionStart;
        $this->sessionEnd = $sessionEnd;
    }

    /**
     * Create a SessionDTO instance from a CourseSession model.
     *
     * @param  mixed $session
     * @return self
     */
    public static function fromModel($session): self
    {
        return new self(
            $session->id,
            $session->room,
            $session->session_start,
            $session->session_end
        );
    }
}
