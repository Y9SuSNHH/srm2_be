<?php

namespace App\Http\Domain\TrainingProgramme\Services;

use App\Helpers\CsvParser;
use App\Http\Domain\TrainingProgramme\Repositories\StudyPlan\StudyPlanRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Requests\StudyPlan\SearchRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class StudyPlanService
{

    /** @var \App\Http\Domain\TrainingProgramme\Repositories\StudyPlan\StudyPlanRepositoryInterface */
    private $study_plan_repository;
    /**
     * StudyPlanService constructor.
     * @param StudyPlanRepositoryInterface $study_plan_repository
     */
    public function __construct(StudyPlanRepositoryInterface $study_plan_repository)
    {
        $this->study_plan_repository = $study_plan_repository;
    }

    /**
     * @param SearchRequest $request
     */
    public function getList(SearchRequest $request)
    {
        $study_plan = $this->study_plan_repository->getAll($request);
        $prices = $this->study_plan_repository->getListPrice();

        $study_plan->map(function ($study_plan) use ($prices) {
            if ($study_plan['enrollmentWave']) {
                foreach ($prices as $price) {
                    if (strtotime($price['effective_date']) <= strtotime($study_plan['enrollmentWave']['first_day_of_school'])) {
                        return $study_plan['enrollmentWave']['credit_price'] = $price;
                    }
                }
                if (is_null($study_plan['enrollmentWave']['credit_price'])) {
                    return $study_plan['enrollmentWave']['credit_price'] = $prices[0] ?? null;
                }
            }
        });

        return $study_plan;
    }

    /**
     * @param SearchRequest $request
     */
    public function getListExport(SearchRequest $request)
    {
        $study_plan = $this->study_plan_repository->export($request);
        $prices = $this->study_plan_repository->getListPrice();

        $study_plan->map(function ($study_plan) use ($prices) {
            if ($study_plan['enrollmentWave']) {
                foreach ($prices as $price) {
                    if (strtotime($price['effective_date']) <= strtotime($study_plan['enrollmentWave']['first_day_of_school'])) {
                        return $study_plan['enrollmentWave']['credit_price'] = $price;
                    }
                }
                if (is_null($study_plan['enrollmentWave']['credit_price'])) {
                    return $study_plan['enrollmentWave']['credit_price'] = $prices[0] ?? null;
                }
            }
        });

        return $study_plan;
    }

    /**
     * @return array
     */
    public static function getLabels(): array
    {
        return [
            'A' => 'STT',
            'B' => 'Trạm',
            'C' => 'Ngày KG',
            'D' => 'Ngành',
            'E' => 'Lớp',
            'F' => 'Đợt',
            'G' => 'Slot',
            'H' => 'Mã học phần',
            'I' => 'Môn',
            'J' => 'Tín chỉ',
            'K' => 'Ngày bắt đầu',
            'L' => 'Ngày kết thúc',
            'M' => 'Ngày thi',
            'N' => 'Đơn giá',
            'O' => 'Học phí',
        ];
    }

    /**
     * @return array
     */
    public function createTemplateFile(SearchRequest $request): array
    {
        $study_plan = $this->getListExport($request);
        $data = array();
        foreach ($study_plan as $key => $val) {
            if ($val->studyPlans->count() > 0) {
                foreach ($val->studyPlans as $index => $study) {
                    if ($index == 0) {
                        $row['A']  =  $key + 1;
                        $row['B']  =  $val->area ? $val->area->name : null;
                        $row['C']  =  $val->enrollmentWave ? date('d/m/Y', strtotime($val->enrollmentWave->first_day_of_school)) : null;
                        $row['D']  =  $val->major ? $val->major->shortcode : null;
                        $row['E']  =  $val->code ? $val->code : null;
                    } else {
                        $row['A']  =  null;
                        $row['B']  =  null;
                        $row['C']  =  null;
                        $row['D']  =  null;
                        $row['E']  =  null;
                    }
                    $row['F']  =  $study->semester;
                    $row['G']  =  $study->slot;
                    $row['H']  =  $study->learningModule ? $study->learningModule->code : null;
                    $row['I']  =  $study->learningModule ? $study->learningModule->subject->name : null;
                    $row['J']  =  $study->learningModule ? $study->learningModule->amount_credit : 0;
                    $row['K']  =  $study->study_began_date ? date('d/m/Y', strtotime($study->study_began_date)) : null;
                    $row['L']  =  $study->study_ended_date ? date('d/m/Y', strtotime($study->study_ended_date)) : null;
                    $row['M']  =  $study->day_of_the_test ? date('d/m/Y', strtotime($study->day_of_the_test)) : null;
                    $row['N']  =  $val->enrollmentWave ? $val->enrollmentWave->credit_price['price'] : 1;
                    $row['O']  =  $row['J'] * $row['N'];
                    $data[]  =  $row;
                }
            } else {
                $row['A']  =  $key + 1;
                $row['B']  =  $val->area ? $val->area->name : null;
                $row['C']  =  $val->enrollmentWave ? date('d/m/Y', strtotime($val->enrollmentWave->first_day_of_school)) : null;
                $row['D']  =  $val->major ? $val->major->shortcode : null;
                $row['E']  =  $val->code ? $val->code : null;
                $row['F']  =  null;
                $row['G']  =  null;
                $row['H']  =  null;
                $row['I']  =  null;
                $row['J']  =  null;
                $row['K']  =  null;
                $row['L']  =  null;
                $row['M']  =  null;
                $row['N']  =  null;
                $row['O']  =  null;
                $data[]  =  $row;
            }
        }
        $tmp_file = CsvParser::createCsvUTF8BOMTmp(array_merge([self::getLabels()], $data));
        return $tmp_file ? stream_get_meta_data($tmp_file) : [];
    }

    /**
     * getParams
     *
     * @return array
     */
    public function getParams(): array
    {
        $study_plan = $this->study_plan_repository->getCodeAndAccount();
        $params = array();
        $study_plan = collect($study_plan)->chunk(100);
        foreach ($study_plan as $key => $val) {
            foreach($val as $val2) {
                if ($val2['learning_module_code'] && $val2['account']) {
                    $params[$key]['learning_module_code'][] = $val2['learning_module_code'];
                    $params[$key]['account'][] = $val2['account'];
                }
            }
        }
        $result_params = array();
        foreach ($params as $key => $value){
            $result_params[$key]['learning_module_code'] = array_unique($value['learning_module_code']);
            $result_params[$key]['account'] = array_unique($value['account']);
        }
        return $result_params;
    }

    public function getParams2()
    {
        $result_params = $this->study_plan_repository->getCodeAndAccount();
        return $result_params;
    }
}
