<?php

namespace App\Helpers\Utils;

use App\Eloquent\School;

class GetSchool
{
    /** @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null|School  */
    private $school;
    private static $instance;

    /**
     * @param string|null $key
     * @param string|null $school_code
     * @return static
     */
    public static function singleton(?string $key, string $school_code = null): static
    {
        if (!is_array(self::$instance)) {
            self::$instance = [];
        }

        if (!isset(self::$instance[$key]) || !self::$instance[$key]) {
            self::$instance[$key] = new static($school_code);
        }

        return self::$instance[$key];
    }

    /**
     * GetSchool constructor.
     * @param string|null $school_code
     */
    public function __construct(?string $school_code)
    {
        if ($school_code) {
            $this->school = School::query()->where('school_code', $school_code)->first();
        }
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->school ? $this->school->id : null;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->school ? (string)$this->school->school_code : '';
    }
}
