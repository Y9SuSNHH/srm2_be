<?php

namespace App\Http\Domain\Student\Services;

use App\Helpers\CsvParser;
use App\Http\Domain\Student\Requests\Student\SearchRequest;
use App\Http\Domain\Workflow\Services\WorkflowService;
use App\Http\Domain\Student\Repositories\Student\StudentRepositoryInterface;
use App\Http\Domain\Student\Requests\Student\ApproveStudentUpdateRequest;
use App\Http\Enum\WorkflowApplyDiv;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use ReflectionException;
use Throwable;

class StudentService
{
    /**
     * student_repository
     *
     * @var mixed
     */
    private $student_repository;


    /**
     * __construct
     *
     * @param mixed $student_repository
     * @return void
     */
    public function __construct(StudentRepositoryInterface $student_repository)
    {
        $this->student_repository = $student_repository;
    }

    /**
     * getList
     *
     * @return void
     */
    public function getStudents()
    {
        $student = $this->student_repository->getListItems();
        return $student;
    }

    /**
     * @param int $student_id
     * @param ApproveStudentUpdateRequest $request
     * @return \App\Eloquent\Workflow|null
     */
    public function createStudentApprovalRequest(int $student_id, ApproveStudentUpdateRequest $request): ?\App\Eloquent\Workflow
    {
        $student_model = $this->student_repository->getById($student_id);
        /** @var WorkflowService $workflow_service */
        $workflow_service = app()->service(WorkflowService::class);
        /** @var $workflow_structure */
        [$workflow_structure] = $workflow_service->getStructures(WorkflowApplyDiv::EDIT_STUDENT, WorkflowApplyDiv::EDIT_STUDENT()->getKey());
        /** @var \App\Http\Domain\Student\Form\StudentForm $student_form */
        $student_form = app($workflow_structure->model, [
            'workflow_structure_id' => $workflow_structure->id,
            'student_id'          => $student_model->id,
            'title'                 => "MSV {$student_model->student_code} MHS {$student_model->profile_code} {$student_model->fullname}",
            'description'           => $request->description,
            'values'                => [
                'student_id'           => $student_model->id,
                'decision_no'          => $request->decision_no,
                'decision_date'        => $request->decision_date,
                'decision_return_date' => $request->decision_return_date,
                'student_code'         => $request->student_code,
                'classroom_id'         => $request->classroom_id,
                'student_status'       => $request->student_status,
            ],
        ]);

        return $workflow_service->createWorkflow($workflow_structure, $student_form);
    }

    /**
     * @param int $id
     * @return \App\Http\Domain\Student\Models\Student\Student
     */
    public function getById(int $id): \App\Http\Domain\Student\Models\Student\Student
    {
        return $this->student_repository->getById($id);
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ReflectionException
     * @throws Throwable
     */
    public function update(int $id, array $data): bool
    {
        return (bool)$this->student_repository->update($id, $data);

    }

    public function updateStudentProfile(int $id, array $data): bool
    {
        return $this->student_repository->updateStudentProfile($id, $data);
    }

    /**
     * @return array[]
     */
    public static function exportLabels(): array
    {
        return [
            [
                'A' => 'STT',
                'B' => 'Mã hồ sơ',
                'C' => 'Mã Sinh viên',
                'D' => 'Họ và đệm',
                'E' => 'Tên',
                'F' => 'Giới tính',
                'G' => 'Ngày sinh',
                'H' => 'Nơi sinh',
                'I' => 'Số QĐNH',
                'J' => 'Ngày quyết định',
                'K' => 'Tên lớp',
                'L' => 'QLHT',
                'M' => 'Tài khoản học tập',
                'N' => 'Địa chỉ liên hệ',
                'O' => 'Trạng thái sinh viên',
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
        $validated = Arr::except($request->validated(), ['page', 'per_page', 'keyword']);
        $data      = self::exportLabels();
        if (!empty($validated)) {
            $students = $this->student_repository->getAll($request, true);
            foreach ($students as $key => $student) {
                $student_profile = optional($student->student_profile);
                $profile         = $student_profile['profile'] ?? [];
                $document        = $student->documents;
                $classroom       = $student->classroom;
                $staff           = $classroom['staff'];
                $data[]          = [
                    'A' => $key + 1,
                    'B' => $student->profile_code,
                    'C' => $student->student_code,
                    'D' => $profile['firstname'] ?? null,
                    'E' => $profile['lastname'] ?? null,
                    'F' => $student->gender ?? null,
                    'G' => $profile['birthday'] ? date('d/m/Y', strtotime($profile['birthday'])) : null,
                    'H' => $profile['borned_place'] ?? null,
                    'I' => $document->decision_no,
                    'J' => $document->decision_date,
                    'K' => $classroom['code'] ?? null,
                    'L' => $staff['fullname'] ?? null,
                    'M' => $student->account,
                    'N' => $profile['address'] ?? null,
                    'O' => $student->student_status_name,
                ];
            }
        }

        $temp_file = CsvParser::createCsvUTF8BOMTmp($data);
        return $temp_file ? stream_get_meta_data($temp_file) : [];
    }

    /**
     * @param int $id
     * @param array $update_attributes
     * @return bool
     * @throws ReflectionException
     * @throws Throwable
     */
    public function updateLearningInfo(int $id, array $update_attributes): bool
    {
        return $this->student_repository->update($id, [
            'account' => $update_attributes['account'],
            'email' => $update_attributes['email'],
        ]);
    }
}
