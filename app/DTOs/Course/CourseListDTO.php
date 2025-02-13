<?php

namespace App\DTOs\Course;

class CourseListDTO
{
    public int $id;
    public string $code;
    public string $name;

    public function __construct(int $id, string $code, string $name)
    {
        $this->id   = $id;
        $this->code = $code;
        $this->name = $name;
    }

    /**
     * Create a CourseListDTO instance from a Course model.
     *
     * @param mixed $course
     * @return self
     */
    public static function fromModel($course): self
    {
        return new self(
            $course->id,
            $course->code,
            $course->name
        );
    }
}
