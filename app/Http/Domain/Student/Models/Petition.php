<?php

namespace App\Http\Domain\Student\Models;

use App\Eloquent\Petition as EloquentPetition;
use App\Helpers\Json;
use App\Http\Enum\PetitionContentType;
use App\Http\Enum\PetitionStatus;
use ReflectionException;

class Petition extends Json
{
    public $id;
    public $student_id;
    public $content_type;
    public $content_type_name;
    public $status;
    public $status_name;
    public $current_content;
    public $new_content;
    public $effective_date;
    public $storage_file_id;
    public $no;
    public $date_of_amendment;
    public $latest_petition_flow;
    public $petition_flows;
    public $student;
    public $created_at;

    /**
     * @throws ReflectionException
     */
    public function __construct(EloquentPetition $petition)
    {
        $content_type_name    = PetitionContentType::from($petition->content_type)->getLang();
        $current_content      = json_decode($petition->current_content);
        $new_content          = json_decode($petition->new_content);
        $status_name          = PetitionStatus::from($petition->status)->getLang();
        $latest_petition_flow = new PetitionFlow($petition->petitionFlows[0]);
        $petition_flows       = $petition->petitionFlows->transform(function ($student) {
            return new PetitionFlow($student);
        });

        parent::__construct(array_merge($petition->toArray(), [
            'content_type_name'    => $content_type_name,
            'current_content'      => $current_content,
            'new_content'          => $new_content,
            'status_name'          => $status_name,
            'latest_petition_flow' => $latest_petition_flow,
            'petition_flows'       => $petition_flows,
        ]));
    }

    public static function dates(): array
    {
        return [
            'effective_date',
            'date_of_amendment',
            'created_at',
            'student.classroom.enrollment_wave.first_day_of_school'
        ];
    }
}