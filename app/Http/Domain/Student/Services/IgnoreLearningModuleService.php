<?php

namespace App\Http\Domain\Student\Services;

use App\Helpers\CsvParser;
use App\Http\Domain\Student\Repositories\IgnoreLearningModule\IgnoreLearningModuleRepositoryInterface;
use App\Http\Domain\Student\Repositories\LearningModule\LearningModuleRepositoryInterface;
use App\Http\Domain\Student\Repositories\Student\StudentRepositoryInterface;
use App\Http\Domain\Student\Requests\IgnoreLearningModule\ImportRequest;
use App\Http\Domain\Student\Requests\IgnoreLearningModule\SearchRequest;
use App\Http\Domain\Student\Requests\IgnoreLearningModule\StoreRequest;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use ReflectionException;

class IgnoreLearningModuleService
{
    private IgnoreLearningModuleRepositoryInterface $repository;

    public function __construct(IgnoreLearningModuleRepositoryInterface $ignore_learning_module_repository)
    {
        $this->repository = $ignore_learning_module_repository;
    }


    /**
     * @param int $id
     * @param StoreRequest $request
     * @return array
     * @throws ValidationException
     * @throws ReflectionException
     */
    public function add(int $id, StoreRequest $request): array
    {
        $validate = $request->validated();
        $data     = [];
        foreach ($validate['learning_module'] as $learning_module_id) {
            $data[] = [
                'created_by'         => auth()->getId(),
                'student_id'         => $id,
                'learning_module_id' => $learning_module_id,
            ];
        }
        $this->repository->insert($data);
        return [];
    }

    public static function getLabels(): array
    {
        return [
            'A' => 'STT',
            'B' => 'MÃ SV',
            'C' => 'HỌ',
            'D' => 'TÊN',
            'E' => 'MÃ MÔN',
            'F' => 'TÊN MÔN',
            'G' => 'TÍN CHỈ',
        ];
    }

    /**
     * @param ImportRequest $request
     * @param StudentRepositoryInterface $student_repository
     * @param LearningModuleRepositoryInterface $learning_module_repository
     * @return array
     * @throws ValidationException
     */
    public function importValidator(ImportRequest $request, StudentRepositoryInterface $student_repository, LearningModuleRepositoryInterface $learning_module_repository): array
    {
        $errors              = [];
        $preview             = [];
        $data                = [];
        $validated           = $request->validated();
        $csv                 = new CsvParser($validated['file'], self::getLabels());
        $now                 = Carbon::now();
        $get_learning_module = ['id', 'code', 'amount_credit'];
        $student_codes       = [];
        $codes               = [];
        $rows                = [];
        $valid_data          = [];
        if (!is_null($csv->getErrors())) {
            return [[false], [], []];
        }
        $csv->each(function ($row, $index, $is_not_matches) use (&$valid_data, &$student_codes, &$rows, &$errors) {
            $row = array_map('trim', $row);

            if (empty(array_filter($row))) {
                return;
            }
            if ($is_not_matches) {
                $errors[$index] = ['Số lượng cột không hợp lệ'];
                return;
            }

            $fields = [
                'B' => 'Mã SV',
                'E' => 'Mã học phần',
                'G' => 'Tín chỉ',
            ];

            $data = [
                'student_code'  => trim($row['B']),
                'code'          => trim($row['E']),
                'amount_credit' => trim($row['G']),
            ];

            if (!in_array($data, $valid_data)) {
                $valid_data[] = $data;
                if (empty(trim($row['B'])) || empty(trim($row['G'])) || empty(trim($row['E']))) {
                    $errors[$index] = $this->checkFieldsRowImportEmpty($row, $fields);
                }
            } else {
                $message = 'Trùng dữ liệu import';
                self::checkToCreateMessage($errors, $index, $message);
            }

//            if (!empty(trim($row['B']))) {
//                $student_codes[] = $row['B'];
//            }
//            if (!empty(trim($row['E']))) {
//                $learning_module['code'][] = $row['E'];
//            }
//            if (!empty(trim($row['G']))) {
//                $learning_module['amount_credit'][] = $row['G'];
//            }
            $rows[$index] = $row;
        });

        foreach ($valid_data as $each) {
            $codes[]         = $each['code'];
            $student_codes[] = $each['student_code'];
        }
        $learning_modules = $learning_module_repository->getByCodes($codes, $get_learning_module);
        $students         = $student_repository->getByStudentCode($student_codes, ['id', 'student_code']);
        $ignore_lms       = $this->repository->getByLearningModuleIds($learning_modules->pluck('id')->toArray(), ['student_id', 'learning_module_id']);
        $arr_ignore_lms   = $ignore_lms->toArray();

        foreach ($rows as $index => $row) {
            $learning_module = $learning_modules->where('code', $row['E'])->where('amount_credit', $row['G'])->first();

            $student  = $students->where('student_code', $row['B'])->first();
            $validate = [
                'learning_module_id' => $learning_module->id ?? null,
                'student_id'         => $student->id ?? null,
            ];

            if (in_array($validate, $arr_ignore_lms)) {
                self::checkToCreateMessage($errors, $index, "Dữ liêu đã tồn tại");
            } else {
                if (!empty($row['E'])) {
                    if (is_null($learning_module)) {
                        $message = "Sai Mã học phần hoặc số tín chỉ";
                        self::checkToCreateMessage($errors, $index, $message);
                    } else {
                        $data[$index]['learning_module_id'] = $learning_module->id;
                    }
                }
                if (!empty($row['B'])) {
                    if (is_null($student)) {
                        $message = "Mã sinh viên sai";
                        self::checkToCreateMessage($errors, $index, $message);
                    } else {
                        $data[$index]['student_id'] = $student->id;
                    }
                }
            }
            $preview[] = [
                'row'                => $index,
                'learningModuleName' => $row['F'],
                'learningModuleCode' => $row['E'],
                'amountCredit'       => $row['G'],
                'studentCode'        => $row['B'],
                'errors'             => array_key_exists($index, $errors) ? $errors[$index] : null,
            ];
//            $preview[$index]['row']            = $index;
//            $preview[$index]['learningModule'] = $row['F'] . "($learning_module->code)";
//            $preview[$index]['amountCredit']   = $learning_module->amount_credit;
//            $preview[$index]['studentCode']    = $student->id;
//            $preview[$index]['errors']         = array_key_exists($index, $errors) ? $errors[$index] : '';
        };
        usort($preview, function ($a, $b) {
            return $a['row'] - $b['row'];
        });
        return [$errors, array_values($preview), array_values($data)];
    }

    /**
     * @param array $row
     * @param array $fields
     * @return string
     */
    public function checkFieldsRowImportEmpty(array $row, array $fields): string
    {
        $errors          = '';
        $check_has_error = false;
        foreach ($row as $field => $value) {
            if (array_key_exists($field, $fields)) {
                if (empty(trim($value))) {
                    $errors          .= ($check_has_error ? "<br>" : '') . __('validation.required', ['attribute' => $fields[$field]]);
                    $check_has_error = true;
                }
            }
        }
        return $errors;
    }

    /**
     * @return array
     */
    public function downloadTemplate(): array
    {
        $temp_file = CsvParser::createCsvUTF8BOMTmp([self::getLabels()]);
        return $temp_file ? stream_get_meta_data($temp_file) : [];
    }

    /**
     * @param array $errors
     * @param int $index
     * @param string $message
     * @return void
     */
    public static function checkToCreateMessage(array &$errors, int $index, string $message = ''): void
    {
        if (array_key_exists($index, $errors)) {
            $errors[$index] .= "<br>" . $message;
        } else {
            $errors[$index] = $message;
        }
    }


    public function export(SearchRequest $request): array
    {
        $data      = [];
        $temp_file = CsvParser::createCsvUTF8BOMTmp($data);
        return $temp_file ? stream_get_meta_data($temp_file) : [];
    }
}