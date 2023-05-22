<?php

namespace App\Http\Domain\Api\Controllers;

use App\Http\Domain\Api\Repositories\Staff\StaffRepositoryInterface;
use App\Http\Domain\Api\Requests\Staff\SearchRequest;
use Laravel\Lumen\Routing\Controller;

class StaffController extends Controller
{
    public function index()
    {

    }

    public function options(SearchRequest $request, StaffRepositoryInterface $staff_repository)
    {
        return json_response(true, $staff_repository->getOptions($request));
    }
}
