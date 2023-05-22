<?php

namespace App\Http\Domain\Student\Services;

use App\Eloquent\StudentProfile;
use App\Http\Domain\Student\Repositories\Profile\ProfileRepositoryInterface;
use App\Http\Domain\Student\Repositories\Student\StudentRepositoryInterface;
use App\Http\Domain\Student\Repositories\StudentProfile\StudentProfileRepositoryInterface;
use App\Http\Domain\Student\Requests\Profile\UpdateRequest;
use App\Http\Enum\ProfileCurriculumVitae;
use App\Http\Enum\StudentProfileDocument;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use ReflectionException;

class ProfileService
{
    protected ProfileRepositoryInterface $profile_repository;
    protected StudentProfileRepositoryInterface $student_profile_repository;

    public function __construct(ProfileRepositoryInterface $profile_repository, StudentProfileRepositoryInterface $student_profile_repository)
    {
        $this->profile_repository         = $profile_repository;
        $this->student_profile_repository = $student_profile_repository;
    }

    /**
     * @param StudentRepositoryInterface $student_repository
     * @param UpdateRequest $request
     * @param int $student_id
     * @return array
     * @throws ValidationException
     * @throws ReflectionException
     */
    public function update(StudentRepositoryInterface $student_repository, UpdateRequest $request, int $student_id): array
    {
        $validated        = $request->validated();
        $student          = $student_repository->getById($student_id);
        $curriculum_vitae = array_replace(array_combine(ProfileCurriculumVitae::toArray(),
            array_fill(0, count(ProfileCurriculumVitae::toArray()), '')),
            (array)$student->curriculum_vitae);

        foreach ($curriculum_vitae as $key => &$value) {
            if (isset($validated[$key])) {
                $value = $validated[$key];
            }
        }
        $update                     = Arr::except($validated, array_keys($curriculum_vitae));
        $update                     = Arr::except($update, ['documents', 'profile_code', 'classroom_id', 'staff_id']);
        $update['curriculum_vitae'] = json_encode($curriculum_vitae);

        DB::transaction(function () use ($update, $validated, $student_repository, $student) {
            $update = Arr::except($update, ['note']);
            $student_repository->update($student->id, ['note' => $validated['note']]);

            $documents       = array_replace(array_combine(StudentProfileDocument::toArray(),
                array_fill(0, count(StudentProfileDocument::toArray()), '')),
                (array)$student->documents,
                $validated['documents']);
            $student_profile = ['documents' => json_encode($documents)];
//            if (empty($student->student_profile['profile_code'])) {
//                $student_profile['profile_code'] = $validated['profile_code'];
//            }
//            if (empty($student->student_profile['staff_id'])) {
//                $student_profile['staff_id'] = $validated['staff_id'];
//            }
//            if (empty($student->classroom)) {
//                $student_profile['classroom_id'] = $validated['classroom_id'];
//            }
//            dd($student->student_profile['profile']['id']);
            $this->student_profile_repository->update($student->student_profile['id'], $student_profile);
            $this->profile_repository->update($student->student_profile['profile']['id'], $update);
        });
        return [];
    }
}