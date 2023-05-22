<?php

namespace App\Http\Domain\Receivable\Models\ClassroomReceivable;

use App\Eloquent\ClassroomReceivable as EloquentClassroomReceivable;
use App\Helpers\Json;

class ClassroomReceivable extends Json
{
    public $id;
    public $semester;
    public $purpose;
    public $fee;
    public $ended_date;
    public $classroom_id;
    public $major;
    public $count;
    public $code;
    public $staff;
    public $com_expiration_date;

    public function __construct(EloquentClassroomReceivable $receivable)
    {
        // dd($receivable);
        $major               = $receivable->classroom->major->name;
        $count               = count($receivable->classroom->studentClassrooms->filter(function ($object) {
                            return $object->ended_date == null ? $object->student_id : '';
                        })->toArray());
        $code                = $receivable->classroom->code;
        $staff               = $receivable->classroom->staff->fullname;
        parent::__construct(array_merge($receivable->toArray(), [
            'major'               => $major,
            'count'               => $count,
            'code'                => $code,
            'staff'               => $staff,
        ]));
    }

    public static function dates(): array
    {
        return [
            'ended_date',
        ];
    }
}