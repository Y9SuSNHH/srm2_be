<?php

namespace App\Http\Domain\Registration\Controllers;


use App\Http\Domain\Registration\Repositories\Registration\RegistrationRepositoryInterface;
use App\Http\Domain\Registration\Requests\Registration\SearchRequest;
use App\Http\Domain\Registration\Requests\Registration\RegistrationRequest;
use App\Http\Domain\Registration\Services\RegistrationExportFileService;
use App\Helpers\Traits\StepByStep;
use App\Helpers\Traits\FileDownloadAble;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Support\Facades\Http;


/**
 * Class RegistrationController
 * @package App\Http\Domain\Registration\Controllers
 */
class RegistrationController extends Controller
{
    private RegistrationRepositoryInterface $Registration_repository;
    private $service;

    use StepByStep, FileDownloadAble;

    /**
     * @param RegistrationRepositoryInterface $Registration_repository
     */
    public function __construct(RegistrationRepositoryInterface $Registration_repository, RegistrationExportFileService $service)
    {
        $this->Registration_repository = $Registration_repository;
        $this->service = $service;
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
        return json_response(true, $this->Registration_repository->getAll($request), []);
    }

    /**
     * @param RegistrationRepositoryInterface $Registration_repository
     * @param RegistrationRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(RegistrationRepositoryInterface $Registration_repository, RegistrationRequest $request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        $validator = $request->all();
        return json_response(true, $Registration_repository->update($id, $validator));
    }

    /**
     * @param RegistrationRepositoryInterface $Registration_repository
     * @param int $id
     * @return JsonResponse
     */
    public function delete(RegistrationRepositoryInterface $Registration_repository, int $id): JsonResponse
    {
        return json_response(true, $Registration_repository->delete($id));
    }

    /**
     * @param RegistrationRepositoryInterface $Registration_repository
     * @param int $id
     * @return JsonResponse
     */
    public function getById(RegistrationRepositoryInterface $Registration_repository, int $id): JsonResponse
    {
        return json_response(true, $Registration_repository->getById($id));
    }

    /**
     * @param RegistrationRepositoryInterface $Registration_repository
     * @param int $id
     * @return JsonResponse
     */
    public function exportById(int $id)
    {
        $registration = $this->Registration_repository->getById($id);
        
        $updated_at = $registration['updated_at'];
        
        $fullname = $registration['firstname'] . ' ' . $registration['lastname'];
        
        $date_of_birth = date('d-m-Y', strtotime($registration['date_of_birth']));

        $fodt_file_content = $this->service->generateFile($id);

        $base_filename = sprintf('TVU-PhieuHocVien-%s-%s_%s', $fullname, $date_of_birth, strtotime($updated_at));

        $filename_fodt = "$base_filename.fodt";

        $filename_pdf = "$base_filename.pdf";

        if (file_exists(storage_path() . '/app/registration/' . $filename_pdf)){
            return Storage::download('registration/' . $filename_pdf);
        }

        Storage::disk('local')->put('registration/' . $filename_fodt, $fodt_file_content);
        // return Storage::download('registration/' . $filename_fodt);
        if (file_exists(storage_path() . '/app/registration/' . $filename_fodt)) {
            $process = new Process(['/usr/bin/soffice', '--headless', '--convert-to', 'pdf', storage_path() . '/app/registration/' . $filename_fodt, '--outdir', storage_path() . '/app/registration/']);
            $process->setTimeout(null);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            Storage::disk('local')->delete('registration/' . $filename_fodt);
            return Storage::download('registration/' . $filename_pdf);
        } else {
            return response()->json(['error' => 'Không thể tìm thấy file đã được convert!']);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function downloadInit(): \Illuminate\Http\JsonResponse
    {
        $this->initializationStep(['initToken']);
        $token = token_download_generate(3600);
        $this->passStep('initToken');
        return json_response(true, ['token' => $token]);
    }
}
