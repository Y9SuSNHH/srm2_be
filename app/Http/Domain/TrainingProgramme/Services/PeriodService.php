<?php

namespace App\Http\Domain\TrainingProgramme\Services;

use App\Helpers\CsvParser;
use App\Http\Domain\TrainingProgramme\Repositories\Classroom\ClassRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Repositories\StudySession\PeriodRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Requests\StudySession\UploadPeriodRequest;
use Carbon\Carbon;

class PeriodService
{
    /**
     * @return array
     */
    public static function getLabels(): array
    {
        return [
            'A' => 'Lớp quản lý',
            'B' => 'Đợt học số',
            'C' => 'Ngày ký G',
            'D' => 'Ngày bắt đầu thu',
            'E' => 'Ngày kết thúc thu',
            'F' => 'Ngày bắt đầu đợt học',
            'G' => 'Hạn tính COM',
            'H' => 'Ngày kết thúc đợt học',
            'I' => 'Kỳ cuối(yes/no)',
        ];
    }

    /**
     * @return array
     */
    public function createTemplateFile(): array
    {
        $tmp_file = CsvParser::createCsvUTF8BOMTmp([self::getLabels(), [
            'A' => 'DX21NNA1',
            'B' => '2',
            'C' => '15/01/2023',
            'D' => '16/01/2023',
            'E' => '17/01/2023',
            'G' => '18/01/2023',
            'F' => '19/01/2023',
            'H' => '20/01/2023',
            'I' => 'yes',
        ]]);

        return $tmp_file ? stream_get_meta_data($tmp_file) : [];
    }

    /**
     * @param UploadPeriodRequest $request
     * @param PeriodRepositoryInterface $repository
     * @return array
     */
    public function analyzing(UploadPeriodRequest $request, PeriodRepositoryInterface $repository): array
    {
        $errors = [];
        $preview = [self::getLabels()];
        $data = [];
        $csv = new CsvParser($request->file, self::getLabels());
        $now = Carbon::now();
        /** @var ClassRepositoryInterface $class_repository */
        $class_repository = app(ClassRepositoryInterface::class);
        $classrooms = $class_repository->getClassroomCode();
        $periods_uk = $repository->getExistSemester();
        $csv->each(function ($row, $index, $is_not_matches) use (&$errors, &$preview, &$data, $now, $classrooms, &$periods_uk) {
            $row = array_map('trim', $row);

            if (empty(array_filter($row))) {
                return;
            }

            if ($is_not_matches) {
                $errors[$index] = ['Số lượng cột không hợp lệ'];
                return;
            }
            /**
             * @var Carbon $decision_date
             * @var Carbon $collect_began_date
             * @var Carbon $collect_ended_date
             * @var Carbon $learn_began_date
             * @var Carbon $expired_date_com
             * @var Carbon $learn_ended_date
             */
            [
                $error,
                $semester,
                $decision_date,
                $collect_began_date,
                $collect_ended_date,
                $learn_began_date,
                $expired_date_com,
                $learn_ended_date,
                $is_final
            ] = $this->validateRequired($row);

            $classroom_id = !isset($classrooms[$row['A']]) ? null : (int)($classrooms[$row['A']]);

            if (null === $classroom_id) {
                $error[] = __('validation.in', ['attribute' => 'Mã lớp']);
            } elseif (array_search([$classroom_id, $semester], $periods_uk) !== false) {
                $error[] = "Lớp {$row['A']} và đợt {$row['B']} đã có";
            } else {
                $periods_uk[] = [$classroom_id, $semester];
            }

            if (!empty($error)) {
                $errors[$index] = $error;
                return;
            }

            $data[$index] = [
                'school_id' => school()->getId(),
                'classroom_id' => $classroom_id,
                'semester' => $semester,
                'decision_date' => $decision_date ? $decision_date->toAtomString() : '',
                'collect_began_date' => $collect_began_date->toAtomString(),
                'collect_ended_date' => $collect_ended_date->toAtomString(),
                'learn_began_date' => $learn_began_date->toAtomString(),
                'expired_date_com' => $expired_date_com->toAtomString(),
                'learn_ended_date' => $learn_ended_date->toAtomString(),
                'is_final' => $is_final,
                'created_by' => auth()->getId(),
                'created_at' => $now,
            ];

            $preview[] = $row;
        });

        return [$errors, $preview, $data];
    }

    /**
     * @param PeriodRepositoryInterface $repository
     * @param array $data
     * @return bool
     */
    public function store(PeriodRepositoryInterface $repository, array $data): bool
    {
        return $repository->insert($data);

    }

    /**
     * @param array $row
     * @return array
     */
    private function validateRequired(array $row): array
    {
        $errors = [];
        $semester = (int)$row['B'];
        $decision_date = get_carbon_vn($row['C']);
        $collect_began_date = get_carbon_vn($row['D']);
        $collect_ended_date = get_carbon_vn($row['E']);
        $learn_began_date = get_carbon_vn($row['F']);
        $expired_date_com = get_carbon_vn($row['G']);
        $learn_ended_date = get_carbon_vn($row['H']);
        $is_final = 'no' !== strtolower($row['I']);

        if (empty($semester)) {
            $errors[] = __('validation.required', ['attribute' => 'Đợt học số']);
        } elseif (!preg_match('/^[1-9]\d*$/', $row['B'])) {
            $errors[] = __('validation.numeric', ['attribute' => 'Đợt học số']);
        }

        if ($semester !== 1) {
            if (empty($row['C'])) {
                $errors[] = __('validation.required', ['attribute' => 'Ngày ký G']);
            } elseif (!$decision_date) {
                $errors[] = __('validation.date_format', ['attribute' => 'Ngày ký G', 'format' => 'd/m/Y']);
            } elseif ($collect_began_date instanceof Carbon) {
                if ($decision_date->isAfter($collect_began_date)) {
                    $errors[] = __('validation.before_or_equal', ['attribute' => 'Ngày ký G', 'date' => 'Ngày bắt đầu thu']);
                }
            }
        }

        if (empty($row['D'])) {
            $errors[] = __('validation.required', ['attribute' => 'Ngày bắt đầu thu']);
        } elseif (!$collect_began_date) {
            $errors[] = __('validation.date_format', ['attribute' => 'Ngày bắt đầu thu', 'format' => 'd/m/Y']);
        }

        if (empty($row['E'])) {
            $errors[] = __('validation.required', ['attribute' => 'Ngày kết thúc thu']);
        } elseif (!$collect_ended_date) {
            $errors[] = __('validation.date_format', ['attribute' => 'Ngày kết thúc thu', 'format' => 'd/m/Y']);
        } elseif ($collect_began_date && $collect_ended_date->isBefore($collect_began_date)) {
            $errors[] = __('validation.after_or_equal', ['attribute' => 'Ngày kết thúc thu', 'date' => 'Ngày bắt đầu thu']);
        }

        if (empty($row['F'])) {
            $errors[] = __('validation.required', ['attribute' => 'Ngày bắt đầu đợt học']);
        } elseif (!$learn_began_date) {
            $errors[] = __('validation.date_format', ['attribute' => 'Ngày bắt đầu đợt học', 'format' => 'd/m/Y']);
        } elseif ($collect_ended_date && $learn_began_date->isBefore($collect_ended_date)) {
            $errors[] = __('validation.after_or_equal', ['attribute' => 'Ngày bắt đầu đợt học', 'date' => 'Ngày kết thúc thu']);
        }

        if (empty($row['G'])) {
            $errors[] = __('validation.required', ['attribute' => 'Hạn tính COM']);
        } elseif (!$expired_date_com) {
            $errors[] = __('validation.date_format', ['attribute' => 'Hạn tính COM', 'format' => 'd/m/Y']);
        } elseif ($learn_began_date && $expired_date_com->isBefore($learn_began_date)) {
            $errors[] = __('validation.after_or_equal', ['attribute' => 'Hạn tính COM', 'date' => 'Ngày bắt đầu đợt học']);
        }

        if (empty($row['H'])) {
            $errors[] = __('validation.required', ['attribute' => 'Ngày kết thúc đợt học']);
        } elseif (!$learn_ended_date) {
            $errors[] = __('validation.date_format', ['attribute' => 'Ngày kết thúc đợt học', 'format' => 'd/m/Y']);
        } elseif ($expired_date_com && $learn_ended_date->isBefore($expired_date_com)) {
            $errors[] = __('validation.after_or_equal', ['attribute' => 'Ngày kết thúc đợt học', 'date' => 'Hạn tính COM']);
        }

        if (empty($row['I'])) {
            $errors[] = __('validation.required', ['attribute' => 'Kỳ cuối']);
        } elseif (!in_array($row['I'], ['yes', 'no', 'YES', 'NO'])) {
            $errors[] = __('validation.in_array', ['attribute' => 'Kỳ cuối', 'other' => '[yes, no, YES, NO]']);
        }

        return [
            $errors,
            $semester,
            $decision_date,
            $collect_began_date,
            $collect_ended_date,
            $learn_began_date,
            $expired_date_com,
            $learn_ended_date,
            $is_final,
        ];
    }
}
