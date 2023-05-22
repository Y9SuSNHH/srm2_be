<?php

namespace App\Http\Domain\Reports\Factories;

use App\Http\Domain\Reports\Requests\F111\UploadRequest;

interface ProfileServiceInterface
{
    public function processUploadFile(UploadRequest $request): bool;

    public function getErrors(): array;

    public function getPreview(): array;

    public function getProfile(): array;
    
    public function getStudentProfile(): array;
    
    public function getStudent(): array;

    public function getStudentClassroom(): array;
    
    public function getLabels(): array;

    public function validateRequired(array $row): array;
}
