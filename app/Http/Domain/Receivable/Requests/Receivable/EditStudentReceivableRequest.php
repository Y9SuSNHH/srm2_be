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
 * @property $receivable
 * @property $note
 * @property int $student_receivable_id
 */
class EditStudentReceivableRequest extends BaseSearchRequest
{
    public function rules(array $input): array
    {
        return [
            'student_receivable_id' => 'required|int',
            'receivable'            => 'required|int',
            'note'                  => 'nullable|string',
        ];
    }
}
