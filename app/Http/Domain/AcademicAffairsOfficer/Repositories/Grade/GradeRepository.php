<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Repositories\Grade;

use App\Eloquent\Grade;
use App\Eloquent\GradeSetting;
use App\Eloquent\GradeValue;
use App\Eloquent\Student;
use App\Helpers\Interfaces\PaginateSearchRequest;
use App\Helpers\Interfaces\ThrowIfNotAbleInterface;
use App\Helpers\LengthAwarePaginator;
use App\Helpers\Traits\ThrowIfNotAble;
use App\Http\Domain\AcademicAffairsOfficer\Models\Grade\Grade as ModelGrade;
use App\Http\Domain\AcademicAffairsOfficer\Requests\Grade\SearchRequest;
use App\Http\Enum\PerPage;
use App\Http\Enum\RoleAuthority;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GradeRepository implements GradeRepositoryInterface, ThrowIfNotAbleInterface
{
    use ThrowIfNotAble;

    /**
     * @param int $learning_module_id
     * @return Collection
     */
    public function getSetting(int $learning_module_id): Collection
    {
        return GradeSetting::query()
            ->where('learning_module_id', $learning_module_id)
            ->orderByDesc('priority')
            ->get(['id', 'learning_module_id', 'grade_div']);
    }

    /**
     * @param int $learning_module_id
     * @param Carbon $exam_date
     * @return Collection
     */
    public function getGradeExists(int $learning_module_id, Carbon $exam_date): Collection
    {
        return Grade::query()->where('learning_module_id', $learning_module_id)
            ->whereDate('exam_date', $exam_date)
            ->select(['learning_module_id', 'student_id', 'exam_date'])
            ->distinct()->get();
    }

    /**
     * @param SearchRequest $request
     * @return LengthAwarePaginator
     */
    public function getAll(SearchRequest $request): LengthAwarePaginator
    {
        $query = Grade::query()
            ->with([
                'student' => function($query) use ($request) {
                    /** @var Builder $query */
                    $query->with(['classrooms:id,code', 'studentProfile' => function($query) {
                        /** @var Builder $query */
                        $query->with('profile:id,firstname,lastname,birthday')
                            ->select(['id', 'profile_id']);
                    }]);

                    if ($request->classroom_id) {
                        $query->whereHas('classrooms', function ($query) use ($request) {
                            /** @var Builder $query */
                            $query->where('classrooms.id', $request->classroom_id);
                        });
                    }
                },
                'learningModule' => function($query) use ($request) {
                    /** @var Builder $query */
                    $query->with('subject:id,name');
                },
                'gradeValues',
            ])

            ->orderByDesc('exam_date');

        $query->whereHas('student', function ($query) use ($request) {
            /** @var Builder $query */

            if ($request->getKeyword()) {
                $query->where(function ($query) use ($request) {
                    $query->orWhereILike('student_code', $request->getKeyword());
                    $query->orWhereHas('studentProfile', function ($query) use ($request) {
                        $query->whereHas('getProfile', function ($sub) use ($request) {
                            $sub->whereILike(DB::raw("CONCAT(firstname, ' ', lastname)"), $request->getKeyword());
                        });
                    });
                });
            }

            if ($request->classroom_id) {
                $query->whereHas('classrooms', function ($query) use ($request) {
                    /** @var Builder $query */
                    $query->where('classrooms.id', $request->classroom_id);
                });
            } elseif (RoleAuthority::LEARNING_MANAGEMENT()->check()) {
                $query->whereHas('classrooms', function ($query) {
                    /** @var Builder $query */
                    $query->where('staff_id', auth()->user()->getStaffId() ?? '');
                });
            }
        });

        if ($request->keyword) {
            $query->orWhereILike('note', $request->keyword);
        }

        if ($request->learning_module_id) {
            $query->where('grades.learning_module_id', $request->learning_module_id);
        }

        if ($request->start) {
            $query->whereDate('grades.exam_date', '>=', $request->start);
        }

        if ($request->ended) {
            $query->whereDate('grades.exam_date', '<=', $request->ended);
        }

        /** @var LengthAwarePaginator $paginate */
        if(!$request->perPage()) {
            $paginate = $query->makePaginate(PerPage::getDefault());
        } else {
            $paginate = $query->makePaginate($request->perPage());
        }

        return $paginate;
    }

    /**
     * @param array $attributes
     * @param int|null $storage_file_id
     * @return array|null
     * @throws \ReflectionException
     */
    public function insertGrade(array $attributes, int $storage_file_id = null): ?array
    {
        return $this->createAble(Grade::class, function () use ($attributes, $storage_file_id) {
            $ipk = array_column($attributes, 'ipk');
            $auth_id = auth()->getId();
            $now = Carbon::now();
            $attributes = array_map(function ($row) use ($auth_id, $now, $storage_file_id) {
                unset($row['ipk']);
                return array_replace($row, [
                    'created_at' => $now,
                    'created_by' => $auth_id,
                    'learning_module_id' => $row['learning_module_id'],
                    'student_id' => $row['student_id'],
                    'exam_date' => Carbon::parse($row['exam_date']),
                    'note' => $row['note'],
                    'storage_file_id' => $storage_file_id ?? null,
                ]);
            }, $attributes);

            if (!Grade::query()->insert($attributes)) {
                return null;
            }

            $ids = Grade::query()
                ->whereIn(DB::raw('learning_module_id || \'.\' || student_id || \'.\' || exam_date'), $ipk)
                ->selectRaw('id, learning_module_id || \'.\' || student_id || \'.\' || exam_date as "ipk"')
                ->pluck('id', 'ipk')
                ->toArray();

            return empty($ids) ? null : $ids;
        });
    }

    /**
     * @param array $attributes
     * @return bool
     * @throws \ReflectionException
     */
    public function insertGradeValue(array $attributes): bool
    {
        return $this->createAble(GradeValue::class, function () use ($attributes) {
            return GradeValue::query()->insert(array_filter($attributes));
        });
    }

    /**
     * @param int $storage_file_id
     * @return array
     */
    public function getGradeDeleted(int $storage_file_id): array
    {
        return Grade::query()->whereHas('storageFile', function ($query) use ($storage_file_id) {
            $query->where('storage_files.id', $storage_file_id);
        })
            ->pluck('id')->toArray();
    }

    /**
     * @param int $storage_file_id
     * @param array $grade_ids
     * @return bool
     * @throws \ReflectionException
     */
    public function delete(int $storage_file_id, array $grade_ids): bool
    {
        return $this->deleteAble(Grade::class, function () use ($storage_file_id, $grade_ids) {
            $query = Grade::query()->where('storage_file_id', $storage_file_id)->whereIn('id', $grade_ids);
            return (bool)$query->delete();
        }, true);
    }
}
