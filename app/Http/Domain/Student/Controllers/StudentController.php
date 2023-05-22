<?php

namespace App\Http\Domain\Student\Controllers;


use App\Helpers\Traits\FileDownloadAble;
use App\Helpers\Traits\StepByStep;
use App\Http\Domain\Common\Services\StorageFileService;
use App\Http\Domain\Student\Repositories\Student\StudentRepositoryInterface;
use App\Http\Domain\Student\Requests\Profile\UpdateRequest;
use App\Http\Domain\Student\Requests\Student\SearchRequest;
use App\Http\Domain\Student\Requests\Student\G110SearchRequest;
use App\Http\Domain\Student\Requests\Student\UpdateLearningInfoRequest;
use App\Http\Domain\Student\Requests\StudentProfile\UpdateRequest as StudentProfileUpdateRequest;
use App\Http\Domain\Student\Requests\StudentProfile\ImportStudentRequest;
use App\Http\Domain\Common\Model\StorageFile\StorageFile as ModelStorageFile;
use App\Http\Domain\Student\Services\ExportStudentProfileService;
use App\Http\Domain\Student\Services\G110\G110ExportService;
use App\Http\Enum\FileDiv;
use App\Http\Domain\Student\Services\ProfileService;
use App\Http\Domain\Student\Services\StudentProfileService;
use App\Http\Domain\Student\Services\StudentService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller;
use ReflectionException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class StudentController
 * @package App\Http\Domain\Student\Controllers
 */
class StudentController extends Controller
{
    use StepByStep, FileDownloadAble;

    private StudentRepositoryInterface $student_repository;
    private StudentService $service;
    private ExportStudentProfileService $file_temp_download_service;
    private G110ExportService $g110_service;


    private const DOWNLOAD_INIT_TOKEN = 'initToken';

    /**
     * @param StudentRepositoryInterface $student_repository
     * @param StudentService $student_service
     */
    public function __construct(
        StudentRepositoryInterface $student_repository,
        StudentService $student_service,
        ExportStudentProfileService $export_student_profile_temp,
        G110ExportService $g110_service
        )
    {
        $this->student_repository = $student_repository;
        $this->service            = $student_service;
        $this->file_temp_download_service = $export_student_profile_temp;
        $this->g110_service       = $g110_service;
    }

    /**
     * @param SearchRequest $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function index(SearchRequest $request): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->student_repository->getAll($request), []);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return json_response(true, $this->student_repository->getById($id), []);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function showGrades(int $id): JsonResponse
    {
        return json_response(true, $this->student_repository->getGradesById($id), []);
    }


    /**
     * @param int $id
     * @return JsonResponse
     * @throws ReflectionException
     */
    public function showTuition(int $id): JsonResponse
    {
        return json_response(true, $this->student_repository->getTuitionById($id), []);
    }

    /**
     * @param ProfileService $profile_service
     * @param UpdateRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function updateProfile(ProfileService $profile_service, UpdateRequest $request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();

        return json_response(true, $profile_service->update($this->student_repository, $request, $id), []);
    }

    /**
     * @param StudentProfileService $student_profile_service
     * @param StudentProfileUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function updateStudentProfile(StudentProfileService $student_profile_service, StudentProfileUpdateRequest $request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $student_profile_service->update($request, $id), []);
    }


    /**
     * @return JsonResponse
     */
    public function exportInit(): JsonResponse
    {
        $this->initializationStep([self::DOWNLOAD_INIT_TOKEN]);
        $token = token_download_generate(30);
        $this->passStep(self::DOWNLOAD_INIT_TOKEN);
        return json_response(true, ['token' => $token]);
    }


    /**
     * @param SearchRequest $request
     * @return BinaryFileResponse
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function export(SearchRequest $request): BinaryFileResponse
    {
        $this->passesStepOrFail(self::DOWNLOAD_INIT_TOKEN);
        return $this->createDownloadCsvUTF8BOM($this->service->export($request), "danh_sach_sinh_vien.csv");
    }

    public function importInit()
    {
        $this->initializationStep(['initForm', 'validate']);
        $this->setData([]);
        $this->passStep('initForm');
        return json_response(true, ['passed' => 'initForm']);
    }

    public function downloadInit(): \Illuminate\Http\JsonResponse
    {
        $this->initializationStep(['initToken']);
        $token = token_download_generate(3600);
        $this->passStep('initToken');
        return json_response(true, ['token' => $token]);
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->passesStepOrFail('initToken');
        return $this->createDownloadCsvUTF8BOM($this->file_temp_download_service->createTemplateFile(), "uploadstudentprofile.csv");
    }

    public function importProfile(ImportStudentRequest $request, StorageFileService $storage_file_service, StudentProfileService $service)
    {
        $request->throwJsonIfFailed();

        if ($this->checkPassesStep('initForm')) {
            [$errors,$preview,$data] = $service->analyzing($request,$this->student_repository);

            if(!empty($errors)) {
                return json_response(true, ['errors' => $errors, 'data' => null]);
            }

            $model_storage_file = $storage_file_service->putFileToTempStorage($request->file, FileDiv::GRADE_IMPORT);
                $this->passStep('validate');
                $this->setData([
                    'file' => $model_storage_file->toStandardArray(),
                    'data' => $data
                ]);

            return json_response(true, ['errors' => null, 'data' => $preview]);
        }
        return json_response(false);
    }


    public function storeImportProfile(ImportStudentRequest $request, StorageFileService $storage_file_service, StudentProfileService $service) {
        if($this->checkPassesStep('validate')) {

            $data = $this->getData();
            $file   = $storage_file_service->saveFileInStorage(new ModelStorageFile($data['file']));
            $service->store($file->id, $data['data']);
            return json_response(true);
        }

        return json_response(false);
    }

    public function exportG110(G110SearchRequest $request)
    {
        $fods_file_content = $this->g110_service->generateFile($request);
        $base_filename = sprintf('G110_report_%s_%s', date('d-m-Y'), time());
        $filename_fods = "$base_filename.fods";
        Storage::disk('local')->put('G110/'.$filename_fods, $fods_file_content);

        if (file_exists(storage_path().'/app/G110/'.$filename_fods)) {
            $process = new \Symfony\Component\Process\Process(['/usr/bin/soffice', '--headless', '--convert-to', 'xlsx', storage_path().'/app/G110/'.$filename_fods, '--outdir', base_path().'/storage/app/G110/']);
            $process->setTimeout(null);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            Storage::disk('local')->delete('G110/'.$filename_fods);
            $filename_xlsx = "$base_filename.xlsx";
            return Storage::download(sprintf('G110/%s',$filename_xlsx));
        } else {
            return response()->json(['error' => 'Không thể tìm thấy file đã được convert!']);
        }
    }

    /**
     * @param int $id
     * @param UpdateLearningInfoRequest $request
     * @param StudentService $student_service
     * @return JsonResponse
     * @throws Exception|\Throwable
     */
    public function updateLearningInfo(int $id, UpdateLearningInfoRequest $request, StudentService $student_service): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $student_service->updateLearningInfo($id, $request->validated()));
    }
}
