<?php

namespace App\Http\Domain\Student\Services;

use App\Helpers\CsvParser;
use App\Http\Domain\Common\Services\StudentHistoryService;
use App\Http\Domain\Student\Models\Content as ContentModel;
use App\Http\Domain\Student\Repositories\Classroom\ClassroomRepositoryInterface;
use App\Http\Domain\Student\Repositories\Petition\PetitionRepositoryInterface;
use App\Http\Domain\Student\Repositories\PetitionFlow\PetitionFlowRepositoryInterface;
use App\Http\Domain\Student\Repositories\Student\StudentRepositoryInterface;
use App\Http\Domain\Student\Requests\Petition\SearchRequest;
use App\Http\Domain\Student\Requests\Petition\StoreRequest;
use App\Http\Domain\Student\Requests\Petition\UpdateRequest;
use App\Http\Enum\PetitionContent;
use App\Http\Enum\PetitionContentType;
use App\Http\Enum\PetitionFlowStatus;
use App\Http\Enum\PetitionStatus;
use App\Http\Enum\RoleAuthority;
use App\Http\Enum\StudentRevisionHistoryType;
use App\Http\Enum\StudentStatus;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use ReflectionException;

class PetitionService
{
    protected PetitionRepositoryInterface $petition_repository;
    protected StudentRepositoryInterface $student_repository;
    protected PetitionFlowRepositoryInterface $petition_flow_repository;
    protected ClassroomRepositoryInterface $classroom_repository;

    public function __construct(PetitionRepositoryInterface     $petition_repository,
                                StudentRepositoryInterface      $student_repository,
                                PetitionFlowRepositoryInterface $petition_flow_repository,
                                ClassroomRepositoryInterface    $classroom_repository)
    {
        $this->petition_repository      = $petition_repository;
        $this->student_repository       = $student_repository;
        $this->petition_flow_repository = $petition_flow_repository;
        $this->classroom_repository     = $classroom_repository;
    }

    /**
     * @param SearchRequest $request
     * @param bool $is_get_all
     * @return mixed
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function getAll(SearchRequest $request, bool $is_get_all = false): mixed
    {
        $petitions    = $this->petition_repository->getAll($request, $is_get_all)->toArray();
        $classroomIds = [];
        $data         = $petitions['data'] ?? $petitions;
        foreach ($data as $each) {
            if (isset($each->new_content->classroom)) {
                $classroomIds[] = $each->new_content->classroom;
            }
            if (isset($each->current_content->classroom)) {
                $classroomIds[] = $each->current_content->classroom;
            }
        }
        $classrooms = $this->classroom_repository->getByIds(array_filter(array_unique($classroomIds)));
        $classroom  = $classrooms->pluck('code', 'id');
        $area       = $classrooms->pluck('area.name', 'id');

        $first_day_of_school = $classrooms->pluck('enrollment_wave.first_day_of_school', 'id');
        foreach ($data as $each) {
            if ($each->content_type === PetitionContentType::CHANGE_AREA) {
                $each->current_content->area = $area[$each->current_content->classroom] ?? '';
                $each->new_content->area     = $area[$each->new_content->classroom];
            }
            if (isset($each->current_content->classroom)) {
                $each->current_content->classroom_code      = $classroom[$each->current_content->classroom] ?? '';
                $each->current_content->first_day_of_school = $first_day_of_school[$each->current_content->classroom] ?? '';
            }
            if (isset($each->new_content->classroom)) {
                $each->new_content->classroom_code      = $classroom[$each->new_content->classroom];
                $each->new_content->first_day_of_school = $first_day_of_school[$each->new_content->classroom];
            }
            $each->new_content     = new ContentModel($each->new_content);
            $each->current_content = new ContentModel($each->current_content);
        }
        return $petitions;
    }

    /**
     * @param $content_type
     * @param $student_id
     * @return array
     */
    public function getCurrentContent($content_type, $student_id): array
    {
        $student         = $this->student_repository->getById($student_id);
        $current_content = PetitionContentType::getBaseJsonContent($content_type);

        if (empty($student)) {
            return [];
        }

        if (array_key_exists(PetitionContent::STUDENT_STATUS, $current_content)) {
            $current_content[PetitionContent::STUDENT_STATUS] = $student->student_status;
        }
        if (array_key_exists(PetitionContent::CLASSROOM, $current_content)) {
            $current_content[PetitionContent::CLASSROOM] = $student->classroom['id'] ?? '';
        }
        return $current_content;
    }

    /**
     * @param $content_type
     * @param $new_content
     * @return array
     */
    public function getNewContent($content_type, $new_content): array
    {
        $new_content[PetitionContent::STUDENT_STATUS] = PetitionContentType::getNewStudentStatus($content_type);

        $base_content = PetitionContentType::getBaseJsonContent($content_type);

        return array_intersect_key(array_replace($base_content, $new_content), $base_content);
    }

    /**
     * @param StoreRequest $request
     * @param $student_id
     * @return array
     * @throws ValidationException
     */
    public function add(StoreRequest $request, $student_id): array
    {
        $validated           = $request->validated();
        $content_type        = $validated['content_type'];
        $new_content         = $validated['new_content'] ?? [];
        $petition_store_data = array_merge($validated, [
            'student_id'      => $student_id,
            'new_content'     => json_encode(self::getNewContent($content_type, $new_content)),
            'current_content' => json_encode(self::getCurrentContent($content_type, $student_id)),
            'created_by'      => auth()->user()->id,
            'status'          => PetitionStatus::LEARNING_MANAGEMENT_SEND,
        ]);
        DB::transaction(function () use ($petition_store_data, $validated) {
            $petition                 = $this->petition_repository->create($petition_store_data);
            $petition_flow_store_data = [
                'petition_id'       => $petition->id,
                'staff_id'          => auth()->user()->getStaffId(),
                'status'            => PetitionFlowStatus::SEND,
                'role_authority'    => auth()->guard()->roleAuthority(),
                'is_update_student' => false,
            ];
            if (!empty($validated['note'])) {
                $petition_flow_store_data['note'] = $validated['note'];
            }
            $this->petition_flow_repository->create($petition_flow_store_data);
        });
        return [];
    }

    /**
     * @param UpdateRequest $request
     * @param $petition_id
     * @return array
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function update(UpdateRequest $request, $petition_id): array
    {
        $validated = $request->validated();
        $petition  = $this->petition_repository->getWithLatestPetitionFlow($petition_id);

        if (in_array($petition->status, PetitionStatus::thirdParty())) {
            throw new HttpResponseException(errors_response("Đơn từ đã đóng"));
        }
        if ($petition->latestPetitionFlow->role_authority !== RoleAuthority::LEARNING_MANAGEMENT
            && RoleAuthority::LEARNING_MANAGEMENT === auth()->guard()->roleAuthority()) {
            throw new HttpResponseException(errors_response("Không có quyền"));
        }
        DB::transaction(function () use ($validated, $petition) {
            $petition_update_data = [];

            $new_content = (array)json_decode($petition->new_content);
            if (RoleAuthority::LEARNING_MANAGEMENT()->check() || $validated['status'] === PetitionFlowStatus::THIRD_PARTY_ACCEPT) {
                $validated['new_content'] = array_replace($new_content, $validated['new_content'] ?? []);
//                }
//                dd($validated);
                $petition_flow_update = [];
                if (array_key_exists('note', $validated)) {
                    $petition_flow_update['note'] = $validated['note'];
                }
                $this->petition_flow_repository->update($petition->latestPetitionFlow->id, $petition_flow_update);
                $petition_update_data = Arr::except($validated, ['note', 'no', 'date_of_amendment', 'is_update_student']);
            }
            if (RoleAuthority::ACADEMIC_AFFAIRS_OFFICER()->check()) {
                $status              = (int)$validated['status'];
                $petition_flow_store = [
                    'petition_id'       => $petition->id,
                    'staff_id'          => auth()->user()->getStaffId(),
                    'status'            => $status,
                    'role_authority'    => auth()->guard()->roleAuthority(),
                    'is_update_student' => $petition->latestPetitionFlow->is_update_student ? $petition->latestPetitionFlow->is_update_student : $validated['is_update_student'],
                ];
                if (in_array($status, PetitionStatus::reject()) && !array_key_exists('note', $validated)) {
                    throw new HttpResponseException(errors_response("Vui lòng điền nôi dung ghi chú lý do từ chối"));
                }
                if ($status === PetitionStatus::SCHOOL_ACCEPT) {
                    if (!array_key_exists('effective_date', $validated)) {
                        throw new HttpResponseException(errors_response("Vui lòng điền ngày áp dụng"));
                    }
                }
                if (in_array($status, array_merge(PetitionStatus::academicAffair(), PetitionStatus::thirdParty()))) {
                    $petition_update_data = [
                        'status'            => $validated['status'],
                        'date_of_amendment' => $validated['date_of_amendment'],
                    ];
                }
                if (array_key_exists('note', $validated)) {
                    $petition_flow_store['note'] = $validated['note'];
                }
                if ($status === PetitionStatus::SCHOOL_ACCEPT || $validated['is_update_student']) {
                    $validated['new_content'] = array_replace($new_content, $validated['new_content'] ?? []);
                    $petition_update_data     = array_merge($petition_update_data, Arr::except($validated, ['note', 'is_update_student']));
                    /** @var StudentHistoryService $service */
                    $service = app()->service(StudentHistoryService::class);
                    $date    = null;
                    if (array_key_exists('date_of_amendment', $validated) && !$petition->latestPetitionFlow->is_update_student) {
                        $date = new Carbon($validated['date_of_amendment']);
                    }
                    if ($petition->content_type === PetitionContentType::CHANGE_AREA ||
                        $petition->content_type === PetitionContentType::CONTINUE_TO_STUDY_CHANGE_MAJORS) {
                        $service->saveStudentRevisionHistories(StudentRevisionHistoryType::CLASSROOM, $petition->student_id, $validated['new_content']['classroom'], $date, $petition->id);
                    }
                    $service->saveStudentRevisionHistories(StudentRevisionHistoryType::STUDENT_STATUS, $petition->student_id, $validated['new_content']['student_status'], $date, $petition->id);
                    $this->student_repository->update($petition->student_id, ['student_status' => $validated['new_content']['student_status']]);
                }
                $this->petition_flow_repository->create($petition_flow_store);
            }
            if (array_key_exists('new_content', $petition_update_data)) {
                $petition_update_data['new_content'] = json_encode(Arr::except($petition_update_data['new_content'], ['area']));
            }
            if (array_key_exists('effective_date', $petition_update_data) && $petition_update_data['effective_date'] === '') {
                $petition_update_data['effective_date'] = null;
            }
            if (array_key_exists('date_of_amendment', $validated) && $validated['date_of_amendment'] === '') {
                $petition_update_data['date_of_amendment'] = null;
            }
            $this->petition_repository->update($petition->id, $petition_update_data);
        });
        return [];
    }

    /**
     * @param int $id
     * @return array
     */
    public function delete(int $id): array
    {
        DB::transaction(function () use ($id) {
            $this->petition_repository->delete($id);
            $this->petition_flow_repository->deleteByPetitionId($id);
        });
        return [];
    }

    public static function exportLabels(): array
    {
        return [
            ['A' => 'DANH SÁCH BÀN GIAO ĐƠN TỪ'],
            [],
            [
                'A' => 'Ngày xuất báo cáo',
                'B' => Carbon::now()->format('d/m/Y'),
            ],
            [],
            [
                'A'  => 'STT',
                'B'  => 'Mã hồ sơ',
                'C'  => 'Khu vực',
                'D'  => 'Ngày khai giảng',
                'E'  => 'Mã sinh viên',
                'F'  => 'Họ và tên',
                'G'  => 'Số điện thoại',
                'H'  => 'Phái',
                'I'  => 'Ngày sinh',
                'J'  => 'Tài khoản học tập',
                'K'  => 'Mã lớp',
                'L'  => 'QLHT gửi đơn',
                'M'  => 'Trạng thái đơn từ',
                'N'  => 'Trạng thái sinh viên ban đầu',
                'O'  => 'Trạng thái sinh viên chuyển đến',
                'P'  => 'Khu vực chuyển đến',
                'Q'  => 'Ngày KG lớp chuyển đến',
                'R'  => 'Lớp chuyển đến',
                'S'  => 'GV nhận đơn cứng',
                'T'  => 'Ngày QLHT BG đơn',
                'U'  => 'Ngày gửi TVU đơn cứng',
                'V'  => 'Ngày TVU trả quyết định',
                'W'  => 'Trạng thái Duyệt đơn',
                'X'  => 'Số quyết định đơn từ',
                'Y'  => 'Ngày trên quyết định',
                'Z'  => 'Ngày GV hiệu chỉnh',
                'AA' => 'Ghi chú',
            ],
        ];
    }

    /**
     * @param SearchRequest $request
     * @return array
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function export(SearchRequest $request): array
    {
        $data = self::exportLabels();

        foreach ($this->getAll($request, true) as $key => $petition) {
            $latest_petition_flow = $petition->latest_petition_flow ?? null;
            $current_content      = $petition->current_content ?? null;
            $new_content          = $petition->new_content ?? null;
            $student              = $petition->student ?? null;
            $student_profile      = $student['student_profile'] ?? null;
            $classroom            = $student['classroom'] ?? null;
            $area                 = $student['classroom']['area'] ?? null;
            $enrollment_wave      = $student['classroom']['enrollment_wave'] ?? null;
            $staff                = $student['classroom']['staff'] ?? null;
            $profile              = $student['profile'] ?? null;
            $firstname            = $profile['firstname'] ?? '';
            $lastname             = $profile['lastname'] ?? '';
            $fullname             = $firstname . ' ' . $lastname;
            $gender               = $profile['gender'] ?? null;
            $birthday             = $profile['birthday'] ? date('d/m/Y', strtotime($profile['birthday'])) : null;

            $gv_send_date          = false;
            $first_day_of_school   = $new_content->first_day_of_school ?? false;
            $c_first_day_of_school = $current_content->first_day_of_school ?? false;

            if (!$c_first_day_of_school) {
                $ew_first_day_of_school = $enrollment_wave['first_day_of_school'] ?? false;
                if ($ew_first_day_of_school) {
                    $c_first_day_of_school = date('d/m/Y', strtotime($ew_first_day_of_school));
                }
            } else {
                $c_first_day_of_school = date('d/m/Y', strtotime($c_first_day_of_school));
            }
            if ($petition->status === PetitionStatus::ACADEMIC_AFFAIR_SEND) {
                $filter       = array_filter($petition->petition_flows->toArray(), function ($flow) {
                    return $flow->status === PetitionFlowStatus::SEND && $flow->role_authority === RoleAuthority::ACADEMIC_AFFAIRS_OFFICER;
                });
                $gv_send_date = ($filter !== [] ? $filter[0]->created_at : false);
            }

            $school_accept = false;
            if ($petition->status === PetitionStatus::SCHOOL_ACCEPT) {
                $filter        = array_filter($petition->petition_flows->toArray(), function ($flow) {
                    return $flow->status === PetitionFlowStatus::THIRD_PARTY_ACCEPT && $flow->role_authority === RoleAuthority::ACADEMIC_AFFAIRS_OFFICER;
                });
                $school_accept = ($filter !== [] ? $filter[0]->created_at : false);
            }
            $data[] = [
                'A'  => $key + 1,
                'B'  => $student_profile['profile_code'] ?? null,
                'C'  => $area['name'] ?? null,
                'D'  => $c_first_day_of_school,
                'E'  => $student['student_code'] ?? null,
                'F'  => $fullname,
                'G'  => $profile['phone_number'],
                'H'  => !is_null($gender) ? ($gender ? "Nữ" : "Nam") : null,
                'I'  => $birthday,
                'J'  => $student['account'] ?? null,
                'K'  => $classroom['code'] ?? null,
                'L'  => $staff['fullname'] ?? null,
                'M'  => $petition->status_name,
                'N'  => $current_content->student_status_name,
                'O'  => $new_content->student_status_name,
                'P'  => $new_content->area ?? null,
                'Q'  => $first_day_of_school ? date('d/m/Y', strtotime($first_day_of_school)) : null,
                'R'  => $new_content->classroom_code ?? null,
                'S'  => $latest_petition_flow->role_authority !== RoleAuthority::LEARNING_MANAGEMENT ? $latest_petition_flow->staff['fullname'] : null,
                'T'  => date('d/m/Y', strtotime($petition->created_at)),
                'U'  => $gv_send_date ? date('d/m/Y', strtotime($gv_send_date)) : null,
                'V'  => $school_accept ? date('d/m/Y', strtotime($school_accept)) : null,
                'W'  => $petition->status_name,
                'X'  => $petition->no,
                'Y'  => $petition->effective_date ? date('d/m/Y', strtotime($petition->effective_date)) : null,
                'Z'  => $petition->date_of_amendment ? date('d/m/Y', strtotime($petition->date_of_amendment)) : null,
                'AA' => $latest_petition_flow->note,
            ];
        }
//        dd($data);
        $temp_file = CsvParser::createCsvUTF8BOMTmp($data);
        return $temp_file ? stream_get_meta_data($temp_file) : [];
    }
}
