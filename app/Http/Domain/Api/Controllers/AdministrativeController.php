<?php

namespace App\Http\Domain\Api\Controllers;

use App\Http\Domain\Api\Repositories\Administrative\AdministrativeRepositoryInterface;
use Laravel\Lumen\Routing\Controller;

/**
 * Class AdministrativeController
 * @package App\Http\Domain\Api\Controllers
 */
class AdministrativeController extends Controller{

    /**
     * @param AdministrativeRepositoryInterface $administrative_repository
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(AdministrativeRepositoryInterface $administrative_repository): \Illuminate\Http\JsonResponse
    {
        return json_response(true, $administrative_repository->getAll());
    }
}