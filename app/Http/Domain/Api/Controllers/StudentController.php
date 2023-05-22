<?php

namespace App\Http\Domain\Api\Controllers;

use App\Http\Domain\Api\Services\StudentService;
use Illuminate\Http\Request;

class StudentController
{
    public function grade(Request $request, StudentService $service)
    {
        $student_code = $request->get('ma_sinh_vien');

        if (!$student_code) {
            return response()->json([]);
        }

        return response()->json($service->gradeTVU($student_code));
    }
}