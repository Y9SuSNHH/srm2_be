<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Models\Classroom;

use App\Eloquent\Classroom as EloquentClassroom;
use App\Helpers\Json;
use App\Http\Enum\ObjectClassification;

/**
 * Class Classroom
 * @package App\Http\Domain\AcademicAffairsOfficer\Models\Classroom
 */
class Classroom extends Json
{
    public $id;
    public $school_id;
    public $old_id;
    public $major_id;
    public $major_name;
    public $enrollment_object_id;
    public $enrollment_object_name;
    public $enrollment_object_fullname;
    public $area_id;
    public $area_name;
    public $enrollment_wave_id;
    public $enrollment_wave_year;
    public $object_classification_id;
    public $object_classification_name;
    public $first_day_of_school;
    public $staff_id;
    public $learning_management_name;
    public $code;
    public $proposal;
    public $description;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;
    public $students_count;

    public function __construct(EloquentClassroom $classroom)
    {
        $object_classification = $classroom->objectClassification ?? null;
        $learning_management = $classroom->learningManagement ?? null;

        $enrollment_object_name = $classroom->enrollmentObject->name ?? null;
        $enrollment_object_code = $classroom->enrollmentObject->code ?? null;
        $enrollment_object_shortcode = $classroom->enrollmentObject->shortcode ?? null;
        $classification = $classroom->enrollmentObject->classification ?? null;
        // $shortcode_classification = $enrollment_object_shortcode;
        // if($enrollment_object_shortcode && $classification){
        //     $shortcode_classification = $enrollment_object_shortcode . $classification . 'N';
        // }
        $enrollment_object_classification = $classification 
                                            ? ObjectClassification::getValueByKey($classification) 
                                            : null;

        parent::__construct(array_merge($classroom->toArray(), [
            'major_name' => $classroom->major->name ?? null,
            'enrollment_object_name' => $enrollment_object_name,
            'enrollment_object_fullname' => $enrollment_object_shortcode . '-' . 
                                            $enrollment_object_name . ' ' . 
                                            $enrollment_object_classification . '(' .
                                            $enrollment_object_code . $classification . ')',
            'area_name' => $classroom->area->name ?? null,
            'enrollment_wave_year' => optional(optional($classroom->enrollmentWave)->first_day_of_school)->format('Y'),
            'object_classification_name' => $object_classification ? $object_classification->name : null,
            'first_day_of_school' => optional(optional($classroom->enrollmentWave)->first_day_of_school)->toAtomString(),
            'learning_management_name' => $learning_management ? $learning_management->fullname : null,
        ]));
    }
}
