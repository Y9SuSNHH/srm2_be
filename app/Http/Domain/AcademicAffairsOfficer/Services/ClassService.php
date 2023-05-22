<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Services;

use App\Http\Domain\AcademicAffairsOfficer\Repositories\Classroom\ClassRepository;
use App\Http\Domain\AcademicAffairsOfficer\Repositories\Classroom\ClassRepositoryInterface;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom\CreateBatchRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Classroom\UpdateLearningManagementRequest;
use App\Http\Domain\TrainingProgramme\Services\EnrollmentWaveService;
use App\Http\Domain\TrainingProgramme\Services\MajorObjectMapService;
use Carbon\Carbon;

class ClassService
{
    /** @var ClassRepository */
    private $class_repository;

    /**
     * ClassService constructor.
     * @param ClassRepositoryInterface $class_repository
     */
    public function __construct(ClassRepositoryInterface $class_repository)
    {
        $this->class_repository = $class_repository;
    }

    /**
     * @param CreateBatchRequest $request
     * @return array|null
     */
    public function createMultiple(CreateBatchRequest $request): ?array
    {
        $area_id = $request->area_id;
        /** @var MajorObjectMapService $major_object_map_service */
        $major_object_map_service = app()->service(MajorObjectMapService::class);
        $major_objects = optional($major_object_map_service->getByArea($area_id))->toArray();

        if (empty($major_objects)) {
            return null;
        }

        $enrollment_wave_id = $request->enrollment_wave_id;
        /** @var EnrollmentWaveService $enrollment_wave_service */
        $enrollment_wave_service = app()->service(EnrollmentWaveService::class);
        $enrollment_wave_model = $enrollment_wave_service->find($enrollment_wave_id);
        $year = substr($enrollment_wave_model->getSchoolYear(), -2);
        $attributes = [];
        $school_id = school()->getId();
        $auth_id = auth()->getId();
        $now = Carbon::now();
        $code_exists = $this->class_repository->getCodeExists($area_id);
        $results = [];

        foreach ($request->items as $item) {
            $majors = array_filter($major_objects, function ($major_object) use ($item) {
                $major_id = $major_object['major']['id'] ?? null;
                return $major_id === $item['major_id'];
            });

            if (!empty($majors)) {
                $major_code = $majors[key($majors)]['major']['code'];
                $enrollment_objects = array_filter($majors, function ($major) use ($item) {
                    $enrollment_object_id = $major['enrollment_object']['id'] ?? null;
                    return $enrollment_object_id === $item['enrollment_object_id'];
                });

                if (!empty($enrollment_objects)) {
                    foreach ($enrollment_objects as $enrollment_object) {
                        $object_code = $enrollment_object['enrollment_object']['code'];
                        $object_classification = $enrollment_object['enrollment_object']['classification'];
                        $suffixes = array_filter(array_map('trim', explode(',', $item['suffixes'])));

                        foreach ($suffixes as $suffix) {
                            $code = "{$object_code}{$year}{$major_code}{$object_classification}{$suffix}";

                            if (in_array($code, $code_exists)) {
                                $results[] = ['code' => $code, 'status' => 'exists'];
                            } else {
                                $results[] = ['code' => $code, 'status' => 'success'];
                                $attributes[] = [
                                    'school_id' => $school_id,
                                    'area_id' => $area_id,
                                    'enrollment_wave_id' => $enrollment_wave_id,
                                    'major_id' => $item['major_id'],
                                    'enrollment_object_id' => $item['enrollment_object_id'],
                                    'code' => $code,
                                    'created_by' => $auth_id,
                                    'created_at' => $now,
                                ];
                            }
                        }
                    }
                }
            }
        }

        if ($this->class_repository->insert($attributes)) {
            return $results;
        }

        return null;
    }

    /**
     * @param UpdateLearningManagementRequest $request
     * @return bool
     */
    public function assignLearningManagement(UpdateLearningManagementRequest $request): bool
    {
        $ids = array_map('intval', $request->classrooms);

        return (bool)$this->class_repository->updateMultipleRecords($ids, ['staff_id' => $request->staff_id]);
    }

    /**
     * @param array $ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findAll(array $ids): \Illuminate\Database\Eloquent\Collection
    {
        return $this->class_repository->findAll($ids);
    }

    public function findExistedClassrooms(array $codes)
    {
        return $this->class_repository->findClassroomsByCodes($codes);
    }
}
