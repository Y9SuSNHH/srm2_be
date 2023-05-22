<?php

namespace App\Http\Domain\TrainingProgramme\Requests\LearningModule;

use App\Eloquent\LearningModule;
use App\Helpers\Request;
use Illuminate\Validation\Rule;
use App\Helpers\Rules\Unique;

class UpdateLearningModuleRequest extends Request
{
    /**
     * @param array $input
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function rules(array $input): array
    {
        $id = $this->httpRequest()->id ?? null;
        return [
            'school_id' => [
                Rule::exists('schools', 'id'),
            ],
            'subject_id' => [
                'required',
                Rule::exists('subjects', 'id'),
            ],
            'code' => [
                'required',
                function ($attribute, $value, $fail) use ($input, $id) {
                    $query = LearningModule::query()->where('code', $input['code']);
                    $count = $id ? $query->where('id', '<>', $id)->count() : $query->count();

                    if ( $count > 0 ) {
                        return $fail('Mã học phần đã có trong cơ sở dữ liệu');
                    }
                },
            ],
            'amount_credit' => [
                'required',
                'integer',
//                (new Unique(LearningModule::class, 'amount_credit'))
//                    ->where('subject_id', $this->httpRequest()->get('subject_id'))
//                    ->ignore($this->httpRequest()->get('id'))
//                    ->transformMessage(function ($attribute, $value) {
//                        return "Học phần này đã tồn tại";
//                    }),
            ],
            'alias' => [
                'nullable',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'school_id' => 'Mã trường',
            'subject_id' => 'Mã môn học',
            'code' => 'Mã học phần',
            'amount_credit' => 'Số tín chỉ',
            'alias' => 'Alias',
        ];
    }
}
