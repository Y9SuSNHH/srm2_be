<?php

namespace App\Http\Domain\Api\Controllers;

use App\Http\Domain\Api\Requests\BacklogSearchRequest;
use App\Http\Domain\Common\Services\BacklogService;
use Illuminate\Http\JsonResponse;

class BacklogController
{
    /**
     * @param BacklogSearchRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(BacklogSearchRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        /** @var BacklogService $backlog_service */
        $backlog_service = app()->service(BacklogService::class);
        return json_response(true, $backlog_service->getAll($request));
    }

    /**
     * @return JsonResponse
     */
    public function retry(): JsonResponse
    {
        /** @var BacklogService $backlog_service */
        $backlog_service = app()->service(BacklogService::class);
        return json_response($backlog_service->reExecute(request()->all()));
    }
}
