<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Repositories\Handover;

use App\Eloquent\Handover;
use App\Eloquent\Model;
use App\Eloquent\Student;
use App\Eloquent\StudentProfile;
use App\Helpers\Traits\ThrowIfNotAble;
use App\Http\Domain\AcademicAffairsOfficer\Models\Handover as HandoverModel;
use App\Http\Domain\AcademicAffairsOfficer\Models\Student\StudentProfile as StudentProfileModel;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\SearchRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\SearchStudentRequest;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Handover\StoreRequest;
use App\Http\Domain\Common\Requests\BaseSearchRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use ReflectionException;
use Throwable;

class HandoverRepository implements HandoverRepositoryInterface
{
    use ThrowIfNotAble;

    private Model|Builder|\Illuminate\Database\Eloquent\Model $query;
    private string $model;
    private Model|Builder|\Illuminate\Database\Eloquent\Model $query_student_profile;
    private string $model_student_profile;

    public function __construct()
    {
        $this->query                 = Handover::query()->getModel();
        $this->model                 = Handover::class;
        $this->query_student_profile = StudentProfile::query()->getModel();
        $this->model_student_profile = StudentProfile::class;
    }


    /**
     * @param SearchRequest $request
     * @param array $select
     * @param bool $get_all
     * @return mixed
     * @throws ValidationException
     */
    public function getAll(SearchRequest $request, array $select = ['*'], bool $get_all = false): mixed
    {
        $validated = $request->validated();
        $query     = $this->query->newQuery()->withCount('studentProfiles')->with([
            'area:id,code,name',
            'staff:id,fullname'
        ]);
        if (!empty($validated['area_id'])) {
            $query->where('area_id', $validated['area_id']);
        }
        if (!empty($validated['first_day_of_school'])) {
            $query->whereDate('first_day_of_school', $validated['first_day_of_school']);
        }

        $query->latest();

        if ($get_all) {
            $data = $query->get()->transform(function ($handover) {
                return new HandoverModel($handover);
            });
        } else {
            $data = $query->makePaginate($request->perPage());
            $data->getCollection()->transform(function ($handover) {
                return new HandoverModel($handover);
            });
        }
        return $data;
    }

    /**
     * @param StoreRequest $request
     * @return mixed
     * @throws ValidationException
     * @throws ReflectionException
     */
    public function store(StoreRequest $request): mixed
    {
        $validated = $request->validated();

        $validated['created_by'] = auth()->getId();
        $validated['updated_at'] = auth()->getId();
        return $this->createAble($this->model, function () use ($validated) {
            return $this->query->newQuery()->create($validated);
        });
    }

    /**
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws ReflectionException
     * @throws Throwable
     */
    public function updateById(int $id, array $data): mixed
    {
        $data['updated_by'] = auth()->getId();
        return $this->updateAble($this->model, function () use ($id, $data) {
            return $this->query->newQuery()->findOrFail($id)->updateOrFail($data);
        });
    }

    /**
     * @param int $id
     * @return mixed
     * @throws ReflectionException
     */
    public function destroy(int $id): mixed
    {
        return $this->deleteAble($this->model, function () use ($id) {
            return $this->query->newQuery()->findOrFail($id)->delete();
        });
    }

    /**
     * @param int $id
     * @return int
     */
    public function getStudentProfilesCount(int $id): int
    {
        return DB::table('student_profiles')->where('handover_id', $id)->count();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function getWithCountStudentProfile(int $id): mixed
    {
        return $this->query->newQuery()->find($id)->studentProfiles()->count();
    }

    /**
     * @param int $id
     * @param SearchStudentRequest $request
     * @return array|HandoverModel
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function getByIdWithStudentProfiles(int $id, SearchStudentRequest $request): array|HandoverModel
    {
        $validated = $request->validated();

        $handover = $this->query->newQuery()->with('area:id,code')->find($id);

        if (is_null($handover)) {
            return [];
        }

//        if ($handover->is_lock === true && empty($validated['use_handover_id'])) {
//            throw_json_response('Đợt bàn giao đã bị khóa');
//        }

        $first_day_of_school = $handover->first_day_of_school;

        $area_id = $handover->area_id;

        $query = $this->query_student_profile->newQuery()
            ->select([
                'student_profiles.id',
                'profile_code',
                'profile_id',
                'documents',
                'handover_id'
            ])
            ->with([
                'profile:profiles.id,firstname,lastname,birthday,phone_number,gender,borned_place',
                'student' => function ($q) {
                    $q->select('students.id', 'student_profile_id', 'student_status');
                    $q->with([
                        'classroom' => function ($q2) {
                            $q2->select('classrooms.id', 'code', 'major_id');
                            $q2->with(['major:majors.id,code,name']);
                        },
                        'latestStudentRevisionHistory:student_revision_histories.id,student_revision_histories.student_id,value'
                    ]);
                },
            ]);

        $query->whereHas('student.classroom.enrollmentWave', function ($q) use ($first_day_of_school) {
            $q->whereDate('first_day_of_school', $first_day_of_school);
        });

        $query->whereHas('student.classroom', function ($q) use ($area_id) {
            $q->where('area_id', $area_id);
        });

        if (!empty($validated['profile_receive_area'])) {
            $query->whereJsonB('documents', 'profile_receive_area', $validated['profile_receive_area']);
        }
        if (!empty($validated['receive_date'])) {
            $query->whereJsonB('documents', 'receive_date', $validated['receive_date']);
        }
        if (!empty($validated['use_handover_id'])) {
            $query->where('handover_id', $id);
        } else {
            $query->whereNull('handover_id');
        }
        if (!empty($validated['classroom_id'])) {
            $query->whereHas('student.classroom', function ($q) use ($validated) {
                $q->where('classrooms.id', $validated['classroom_id']);
            });
        }
        if (!empty($validated['profile_code'])) {
            $query->where('profile_code', 'iLIKE', "%{$validated['profile_code']}");
        }

        $student_profiles = $query->makePaginate($request->perPage());
        $student_profiles->getCollection()->transform(function (StudentProfile $student_profile) {
            return new StudentProfileModel($student_profile);
        });
        $handover->student_profiles = $student_profiles;
        return new HandoverModel($handover);
    }

    /**
     * @param int $id
     * @param bool $is_in_handover
     * @param array $get
     * @return array|Collection
     */
    public function getStudentProfileIdInHandover(int $id, bool $is_in_handover = false, array $get = ['*'],): array|Collection
    {
        $handover = $this->query->newQuery()->with('area:id,code')->find($id);

        if (is_null($handover)) {
            return [];
        }
        $first_day_of_school = $handover->first_day_of_school;

        $area_id = $handover->area_id;

        $query = $this->query_student_profile->newQuery()->select(['student_profiles.id',]);

        $query->whereHas('student.classroom.enrollmentWave', function ($q) use ($first_day_of_school) {
            $q->whereDate('first_day_of_school', $first_day_of_school);
        });

        $query->whereHas('student.classroom', function ($q) use ($area_id) {
            $q->where('area_id', $area_id);
        });
        if (!$is_in_handover) {
            $query->whereNull('handover_id');
        } else {
            $query->whereNotNull('handover_id');
        }

        return $query->get($get)->pluck('id');
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function getIsLockById(int $id): mixed
    {
        return DB::table('handovers')->where('id', $id)->value('is_lock');
    }

    /**
     * @param int $id
     * @param array $get
     * @return array|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|Builder|Model|null
     */
    public function getById(int $id, array $get = ['*']): array|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|Builder|Model|null
    {
        return $this->query->newQuery()->select($get)->find($id);
    }

    public function getStudentById(int $id, BaseSearchRequest $request, array $get = ['*'])
    {
        $validated = $request->validated();
        $handover  = $this->query->newQuery()->with('area:id,code')->findOrFail($id);

        $first_day_of_school = $handover->first_day_of_school;

        $area_id = $handover->area_id;

        $query = Student::query()->newQuery();
//        $query->select('students.id', 'student_profile_id', 'student_status');

        $query->whereHas('studentProfile', function ($q) use ($validated) {
            $q->whereNull('handover_id');
            if (!empty($validated['profile_receive_area'])) {
                $q->whereJsonB('documents', 'profile_receive_area', $validated['profile_receive_area']);
            }

            if (!empty($validated['receive_date'])) {
                $q->whereJsonB('documents', 'receive_date', $validated['receive_date']);
            }
        });

        $query->whereHas('classroom.enrollmentWave', function ($q) use ($first_day_of_school) {
            $q->whereDate('first_day_of_school', $first_day_of_school);
        });

        $query->whereHas('classroom', function ($q) use ($area_id) {
            $q->where('area_id', $area_id);
        });

        return $query->get($get);
    }
}