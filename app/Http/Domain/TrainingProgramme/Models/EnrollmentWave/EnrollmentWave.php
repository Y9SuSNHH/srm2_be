<?php

namespace App\Http\Domain\TrainingProgramme\Models\EnrollmentWave;

use App\Eloquent\EnrollmentWave as EloquentEnrollmentWave;

use App\Helpers\Json;
use Carbon\Carbon;

class EnrollmentWave extends Json
{
    public $id;
    public $school_id;
    public $area_id;
    public $area_code;
    public $group_number;
    public $first_day_of_school;
    public $enrollment_start_date;
    public $enrollment_end_date;
    public $application_submission_deadline;
    public $tuition_payment_deadline;
    public $locked;
    public $school_year;

    public function __construct(EloquentEnrollmentWave $enrollment_wave)
    {
        if (!$enrollment_wave->area) {
            return parent::__construct($enrollment_wave);
        }

        parent::__construct(array_merge($enrollment_wave->toArray(), [
            'area_code' => $enrollment_wave->area->code,
        ]));
    }

    public static function dates(): array
    {
        return [
            'first_day_of_school',
            'enrollment_start_date',
            'enrollment_end_date',
        ];
    }

    /**
     * @return int
     */
    public function getSchoolYear(): int
    {
        if ($this->school_year) {
            return (int)$this->school_year;
        }

        return Carbon::parse($this->first_day_of_school)->year;
    }
}