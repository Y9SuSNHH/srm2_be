<?php

namespace App\Http\Domain\TrainingProgramme\Models\MajorObjectMap;

use App\Helpers\Json;
use App\Eloquent\MajorObjectMap as EloquentMajorObjectMap;

class MajorObjectMap extends Json
{
    public $id;
    public $major_id;
    public $enrollment_object_id;
    public $major_name;
    public $enrollment_object_shortcode;
    public $enrollment_object_name;
    public $enrollment_object_code;
    public $enrollment_object_classification;
    // public $major_count;

    public function __construct(EloquentMajorObjectMap $major_object_map)
    {
        // $classrooms = $major_object_map->major ? $major_object_map->major->classrooms->count() : 0;
        // $trainingPrograms = $major_object_map->major ? $major_object_map->major->trainingPrograms->count() : 0;
        // $studentProfiles = $major_object_map->major ? $major_object_map->major->studentProfiles->count() : 0;

        parent::__construct(array_merge($major_object_map->toArray(), [
            'major_name' => $major_object_map->major->name ?? '',
            'enrollment_object_shortcode' => $major_object_map->enrollment_object->shortcode ?? '',
            'enrollment_object_name' => $major_object_map->enrollment_object->name ?? '',
            'enrollment_object_code' => $major_object_map->enrollment_object->code ?? '',
            'enrollment_object_classification' => $major_object_map->enrollment_object->classification ?? '',
            // 'major_count' => $classrooms + $trainingPrograms + $studentProfiles,
        ]));
    }
}
