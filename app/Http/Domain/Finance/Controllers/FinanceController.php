<?php

namespace App\Http\Domain\Finance\Controllers;


use App\Http\Domain\Finance\Repositories\Finance\FinanceRepositoryInterface;
use App\Http\Domain\Finance\Requests\Finance\SearchRequest;
use App\Http\Domain\Finance\Requests\Finance\FilterRequest;
use App\Http\Domain\Finance\Requests\Finance\StudentClassRequest;
use App\Http\Domain\Finance\Services\FinanceService;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller;
use App\Helpers\Traits\FileDownloadAble;
use App\Helpers\Traits\StepByStep;
use App\Http\Domain\Finance\Requests\Finance\EditRequest;
use App\Http\Domain\Finance\Requests\Finance\TuitionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Class FinanceController
 * @package App\Http\Domain\Finance\Controllers
 */
class FinanceController extends Controller
{
    use StepByStep, FileDownloadAble;

    private $finance_service;

    public function __construct(FinanceService $finance_service)
    {
        $this->finance_service = $finance_service;
    }

    /**
     * @param SearchRequest $request
     * @param FinanceRepositoryInterface $finance_repository
     * @return JsonResponse
     * @throws \Exception
     */
    public function class(SearchRequest $request, FinanceRepositoryInterface $finance_repository): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $finance_repository->getByClass($request), []);
    }

    public function student(SearchRequest $request, FinanceRepositoryInterface $finance_repository): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $finance_repository->getByStudent($request), []);
    }

    public function tuition(TuitionRequest $request, FinanceRepositoryInterface $finance_repository): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $finance_repository->tuition($request), []);
    }

    public function filter(FilterRequest $request, FinanceRepositoryInterface $finance_repository): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $finance_repository->getFilter($request), []);
    }

    public function studentClass(StudentClassRequest $request, FinanceRepositoryInterface $finance_repository): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $finance_repository->studentClass($request), []);
    }

    public function semesterClass(FinanceRepositoryInterface $finance_repository)
    {
        return json_response(true, $finance_repository->semesterClass(), []);
    }

    public function receiveSemester(FinanceRepositoryInterface $finance_repository, SearchRequest $request)
    {
        $request->throwJsonIfFailed();
        return json_response(true, $finance_repository->receiveSemester($request), []); 
    }

    public function filterStudent(FinanceRepositoryInterface $finance_repository, Request $request): JsonResponse
    {
        return json_response(true, $finance_repository->filterStudent($request['purpose']), []);
    }

    /**
     * exportInit
     *
     * @return JsonResponse
     */
    public function exportInit(): JsonResponse
    {
        $this->initializationStep(['initToken']);
        $token = token_download_generate(30);
        $this->passStep('initToken');
        return json_response(true, ['token' => $token]);
    }
    
    /**
     * export
     *
     * @param  mixed $request
     * @return BinaryFileResponse
     */
    public function export(SearchRequest $request): BinaryFileResponse
    {
        $this->passesStepOrFail('initToken');
        return $this->createDownloadCsvUTF8BOM($this->finance_service->export($request), "ds_thu_theo_lop.csv");
    }

    public function exportTransaction(SearchRequest $request): BinaryFileResponse
    {
        $this->passesStepOrFail('initToken');
        return $this->createDownloadCsvUTF8BOM($this->finance_service->exportTransaction($request), "ds_thu.csv");
    }

    public function exportTuition(TuitionRequest $request): BinaryFileResponse
    {
        $this->passesStepOrFail('initToken');
        return $this->createDownloadCsvUTF8BOM($this->finance_service->exportTuition($request), "ds_hoc_phi.csv");
    }

    public function exportTuitionClass(SearchRequest $request)
    {
        $request->throwJsonIfFailed();
        $fods_file_content = $this->finance_service->generateFile($request);
        $base_filename = sprintf('Tuition_report_%s_%s', date('d-m-Y'), time());
            
        $filename_fods = "$base_filename.fods";

        Storage::disk('local')->put('files/Tuition/'.$filename_fods,$fods_file_content);
        // return Storage::download(sprintf('files/Tuition/%s',$filename_fods));
        
        if (file_exists(storage_path().'/app/files/Tuition/'.$filename_fods)) {
            $process = new \Symfony\Component\Process\Process(['/usr/bin/soffice', '--headless', '--convert-to', 'xlsx', storage_path().'/app/files/Tuition/'.$filename_fods, '--outdir', base_path().'/storage/app/files/Tuition/']);
            $process->setTimeout(null);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            Storage::disk('local')->delete('files/Tuition/'.$filename_fods);
            $filename_xlsx = "$base_filename.xlsx";
            return Storage::download(sprintf('files/Tuition/%s',$filename_xlsx));
        } else {
            return response()->json(['error' => 'Không thể tìm thấy file đã được convert!']);
        }
    }

    public function delete(FinanceRepositoryInterface $finance_repository, int $id): JsonResponse
    {
        return json_response(true, $finance_repository->delete($id), []);
    }

    public function update(FinanceRepositoryInterface $finance_repository, EditRequest $request, int $id): JsonResponse
    {
        $request->throwJsonIfFailed();
        return json_response(true, $finance_repository->update($id, $request), []);
    }
    
}