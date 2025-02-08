<?php

namespace App\DTOs\Session;

class SessionListDTO
{
    public int $id;
    public string $room;
    public string $session_start;
    public string $session_end;

    public function __construct(int $id, string $room, string $session_start, string $session_end)
    {
        $this->id = $id;
        $this->room = $room;
        $this->session_start = $session_start;
        $this->session_end = $session_end;
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
