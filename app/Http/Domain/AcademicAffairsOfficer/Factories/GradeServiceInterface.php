<?php

namespace App\Http\Domain\AcademicAffairsOfficer\Factories;

use App\Http\Domain\AcademicAffairsOfficer\Requests\Grade\ImportRequest;

interface GradeServiceInterface
{
    public function processUploadFile(ImportRequest $request): bool;

    public function getErrors(): array;

    public function getPreview(): array;

    public function getData(): array;

    public function getGradeValues(): array;
}
