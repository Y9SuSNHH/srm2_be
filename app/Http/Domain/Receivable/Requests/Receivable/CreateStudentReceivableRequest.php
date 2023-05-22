<?php

namespace App\Http\Domain\Receivable\Requests\Receivable;

use App\Helpers\Request;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

/**
 * Class StudentReceivable
 * @package App\Http\Request\Receivable
 *
 * @property $student_profile_id
 * @property $receivable
 * @property $purpose
 * @property $learning_wave_number
 * @property $note
 * @property int $student_receivable_id
 */
class CreateStudentReceivableRequest extends BaseSearchRequest
{
    public function rules(array $input): array
    {
        return [
            'student_profile_id'    => 'required|int',
            'receivable'            => 'required|int',
            'purpose'               => 'required|string',
            'learning_wave_number'  => 'required|int',
            'note'                  => 'nullable|string',
            'student_receivable_id' => 'nullable|int',
        ];
    }
}
