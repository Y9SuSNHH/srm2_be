<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Requests\Handover;

use App\Helpers\Request;

class UpdateLockRequest extends Request
{
    protected $casts = [
        'is_lock' => self::CAST_BOOL,
    ];

    public function rules(array $input): array
    {
        return [
            'is_lock' => [
                'required',
                'boolean',
            ],
        ];
    }
}