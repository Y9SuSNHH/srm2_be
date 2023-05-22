<?php

namespace App\Http\Domain\Student\Form;

use App\Http\Domain\Student\Models\Student\StudentProfileDocument;
use App\Http\Domain\Workflow\Form\WorkflowFormInterface;
use App\Http\Enum\ApprovalStatus;
use App\Http\Enum\ProfileStatus;
use App\Http\Enum\StudentStatus;

class StudentForm implements WorkflowFormInterface
{
    /** @var null|int */
    private $workflow_id;
    /** @var null|int */
    private $workflow_structure_id;
    /** @var int|null */
    private $student_id;
    /** @var int */
    private $approval_status;
    /** @var string */
    private $title;
    /** @var string */
    private $description;
    /** @var array */
    private $values;
    /** @var array */
    private $workflow_approvals;
    /** @var int */
    private $step;

    public function __construct(
        int $workflow_id = null,
        int $workflow_structure_id = null,
        int $student_id = null,
        int $approval_status = null,
        int $step = 1,
        string $title = null,
        string $description = null,
        array $workflow_approvals = [],
        array $values = [])
    {
        $this->workflow_id = $workflow_id;
        $this->workflow_structure_id = $workflow_structure_id;
        $this->student_id = $student_id;
        $this->approval_status = $approval_status ?? ApprovalStatus::SENDING;
        $this->title = $title;
        $this->description = $description;
        $this->values = $values;
        $this->workflow_approvals = $workflow_approvals;
        $this->step = $step;
    }

    /**
     * @return array
     */
    public function workflowAttributes(): array
    {
        $attribute = [
            'workflow_structure_id' => $this->workflow_structure_id,
            'approval_status' => $this->approval_status,
            'is_close' => false,
            'title' => $this->title,
            'description' => $this->description,
        ];

        if ($this->student_id) {
            $attribute['reference_id'] = $this->student_id;
        }

        return $attribute;
    }

    public function workflowValueAttributes(): array
    {
        return [
            ['target' => 'student_id', 'value' => $this->values['student_id'] ?? ''],
            ['target' => 'decision_no', 'value' => $this->values['decision_no'] ?? ''],
            ['target' => 'decision_date', 'value' => $this->values['decision_date'] ?? ''],
            ['target' => 'decision_return_date', 'value' => $this->values['decision_return_date'] ?? ''],
            ['target' => 'student_code', 'value' => $this->values['student_code'] ?? ''],
            ['target' => 'student_status', 'value' => $this->values['student_status'] ?? ''],
            ['target' => 'classroom_id', 'value' => $this->values['classroom_id'] ?? ''],
        ];
    }

    /**
     * @param array|null $values
     * @return mixed
     */
    public function workflowValues(array $values = null): mixed
    {
        if ($values) {
            foreach ($values as $value) {
                $this->values[$value['target']] = $value['value'];
            }

            return true;
        }

        return array_filter([
            'studentId' => $this->values['student_id'] ?? '',
            'decisionNo' => $this->values['decision_no'] ?? '',
            'decisionDate' => $this->values['decision_date'] ?? '',
            'decisionReturnDate' => $this->values['decision_return_date'] ?? '',
            'studentCode' => $this->values['student_code'] ?? '',
            'classroom_id' => $this->values['classroom_id'] ?? '',
            'studentStatus' => $this->values['student_status'] ?? '',
        ], fn($value) => !empty($value));
    }

    public function workflowApprovalAttributes(): array
    {
        return [];
    }

    public function getStudentId(): ?int
    {
        return isset($this->values['student_id']) ? (int)$this->values['student_id'] : null;
    }

    public function getClassroomId(): ?int
    {
        return isset($this->values['classroom_id']) ? (int)$this->values['classroom_id'] : null;
    }

    /**
     * @return mixed
     */
    public function getProfileStatus(): mixed
    {
        return ProfileStatus::fromOptional(!isset($this->values['profile_status']) ? 0 : (int)$this->values['profile_status'])->getValue();
    }

    /**
     * @return mixed
     */
    public function getStudentStatus(): mixed
    {
        return StudentStatus::fromOptional(!isset($this->values['student_status']) ? 0 : (int)$this->values['student_status'])->getValue();
    }

    public function getApprovalStudentAttribute(int $user_id): array
    {
        return array_filter([
            'student_code' => isset($this->values['student_code']) ? $this->values['student_code'] : null,
            'classroom_id' => isset($this->values['classroom_id']) ? $this->values['classroom_id'] : null,
            'profile_status' => $this->getProfileStatus(),
            'student_status' => $this->getStudentStatus(),
            'note' => isset($this->values['note']) ? $this->values['note'] : null,
            'updated_by' => $user_id,
        ]);
    }

    public function getApprovalStudentProfiles(int $user_id, array $document): array
    {
        return array_filter([
            'profile_code' => $this->values['profile_code'] ?? null,
            'staff_id' => $this->values['staff_id'] ?? null,
            'is_ts8' => $this->values['is_ts8'] ?? null,
            'area_id' => $this->values['area_id'] ?? null,
            'major_id' => $this->values['major_id'] ?? null,
            'enrollment_object_id' => $this->values['enrollment_object_id'] ?? null,
            'enrollment_wave_id' => $this->values['enrollment_wave_id'] ?? null,
            'classroom_id' => $this->values['classroom_id'] ?? null,
            'documents' => (new StudentProfileDocument(array_merge($document, $this->values)))->toJson(),
            'updated_by' => $user_id,
        ]);
    }
}
