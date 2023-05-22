<?php

namespace App\Http\Domain\Contact\Controllers;


use App\Http\Domain\Contact\Repositories\Contact\ContactRepositoryInterface;
use App\Http\Domain\Contact\Requests\Contact\SearchRequest;
use App\Http\Domain\Contact\Requests\Contact\ContactRequest;
use App\Http\Domain\Contact\Requests\Contact\AddRequest;
use App\Http\Domain\Contact\Services\ContactService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Support\Facades\Http;


/**
 * Class ContactController
 * @package App\Http\Domain\Contact\Controllers
 */
class ContactController extends Controller
{
    private ContactRepositoryInterface $contact_repository;

    /**
     * @param ContactRepositoryInterface $contact_repository
     */
    public function __construct(ContactRepositoryInterface $contact_repository)
    {
        $this->contact_repository = $contact_repository;
    }

    /**
     * @param SearchRequest $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws Exception
     */

    public function index(SearchRequest $request) : JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->contact_repository->getAll($request), []);
    }

    /**
     * @param ContactService $service
     * @param ContactRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(ContactService $service, ContactRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response($service->insert($request->items));
    }

    /**
     * @param ContactRepositoryInterface $Contact_repository
     * @param AddRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function add(ContactRepositoryInterface $Contact_repository, AddRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $Contact_repository->create($request->all()));
    }

    /**
     * @param ContactRepositoryInterface $Contact_repository
     * @param AddRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(ContactRepositoryInterface $Contact_repository, AddRequest $request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        $validator = $request->all();
        return json_response(true, $Contact_repository->update($id, $validator));
    }

    /**
     * @param ContactRepositoryInterface $Contact_repository
     * @param int $id
     * @return JsonResponse
     */
    public function delete(ContactRepositoryInterface $Contact_repository, int $id): JsonResponse
    {
        return json_response(true, $Contact_repository->delete($id));
    }

    public function link(ContactRepositoryInterface $Contact_repository, int $id): JsonResponse
    {
        return json_response(true, $Contact_repository->link($id), null);
    }
}