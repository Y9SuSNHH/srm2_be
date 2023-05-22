<?php

namespace App\Http\Domain\TrainingProgramme\Services;

use App\Helpers\Traits\CamelArrayAble;
use App\Http\Domain\TrainingProgramme\Models\EnrollmentWave\EnrollmentWave as EnrollmentWaveModel;
use App\Http\Domain\TrainingProgramme\Repositories\EnrollmentWave\EnrollmentWaveRepositoryInterface;
use App\Http\Domain\TrainingProgramme\Requests\EnrollmentWave\CreateEnrollmentWaveRequest;
use Carbon\Carbon;

class EnrollmentWaveService
{
    use CamelArrayAble;

    const SUN_DAY_ISO = 7;

    /** @var \App\Http\Domain\TrainingProgramme\Repositories\EnrollmentWave\EnrollmentWaveRepository */
    private $enrollment_wave_repository;

    /**
     * EnrollmentWaveService constructor.
     * @param EnrollmentWaveRepositoryInterface $enrollment_wave_repository
     */
    public function __construct(EnrollmentWaveRepositoryInterface $enrollment_wave_repository)
    {
        $this->enrollment_wave_repository = $enrollment_wave_repository;
    }

    /**
     * @param int $id
     * @return EnrollmentWaveModel|null
     */
    public function find(int $id): ?EnrollmentWaveModel
    {
        try {
            /** @var $enrollment_wave */
            $enrollment_wave = $this->enrollment_wave_repository->getById($id);
            return new EnrollmentWaveModel($enrollment_wave);
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @param int $id
     * @return array
     */
    public function getDetail(int $id): array
    {
        /** @var EloquentEnrollmentWave $enrollment_wave */
        $enrollment_wave = $this->enrollment_wave_repository->getById($id);

        $weeks = [];
        $day_of_week_iso = $enrollment_wave->enrollment_start_date->dayOfWeekIso;
        $end = $enrollment_wave->enrollment_start_date->copy()->addDays(self::SUN_DAY_ISO - $day_of_week_iso);
        $start = $enrollment_wave->enrollment_start_date;

        while (!$end->isAfter($enrollment_wave->enrollment_end_date)) {
            $weeks[] = [
                'enrollment_start_date' => $start->toAtomString(),
                'enrollment_end_date' => $end->toAtomString(),
            ];

            $start = $end->copy()->addDay();
            $end = $end->addWeek();
        }

        $weeks[] = [
            'enrollment_start_date' => $start->toAtomString(),
            'enrollment_end_date' => $enrollment_wave->enrollment_end_date->toAtomString(),
        ];

        return $this->toCamelArray(array_merge($enrollment_wave->toArray(), ['weeks' => $weeks]));
    }

    /**
     * @param CreateEnrollmentWaveRequest $request
     * @return EnrollmentWaveModel
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(CreateEnrollmentWaveRequest $request): EnrollmentWaveModel
    {
        $validator = $this->addValues($request->validated());
        return $this->enrollment_wave_repository->create($validator);
    }

    /**
     * @param int $id
     * @param CreateEnrollmentWaveRequest $request
     * @return EnrollmentWaveModel
     */
    public function update(int $id, CreateEnrollmentWaveRequest $request): EnrollmentWaveModel
    {
        $validator = $this->addValues($request->all());
        return $this->enrollment_wave_repository->update($id, $validator);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->enrollment_wave_repository->delete($id);
    }

    /**
     * @param array $validator
     * @return array
     */
    private function addValues(array $validator)
    {
        if(empty($validator['application_submission_deadline']))
        {
            $validator['application_submission_deadline'] = Carbon::parse($validator['first_day_of_school'])->subDays(1)->toDateString('Y-m-d');
        }
        if(empty($validator['tuition_payment_deadline']))
        {
            $validator['tuition_payment_deadline'] = Carbon::parse($validator['first_day_of_school'])->addDays(1)->toDateString('Y-m-d');
        }

        return $validator;
    }

    public function findExistedEnrollmentWaves(array $first_day_of_schools)
    {
        return  $this->enrollment_wave_repository->findEnrollmentWave($first_day_of_schools);
    }
}
