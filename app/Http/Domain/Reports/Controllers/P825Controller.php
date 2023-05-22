<?php

namespace App\Http\Domain\Reports\Controllers;



use App\Http\Domain\Reports\Repositories\P825\P825Repository;
use App\Http\Domain\Reports\Repositories\P825\P825RepositoryInterface;
use App\Http\Domain\Reports\Requests\P825\SearchRequest;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Traits\FileDownloadAble;
use App\Helpers\Traits\StepByStep;
use App\Http\Domain\Reports\Services\P825\P825ExportService;
use App\Http\Domain\Reports\Services\P825\P825MainService;
use App\Providers\AuthManager;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Laravel\Lumen\Routing\Controller;
use App\Http\Enum\StudentStatus;

/**
 * Class P825Controller
 * @package App\Http\Domain\Reports\Controllers
 */
class P825Controller extends Controller
{
    /** @var P825RepositoryInterface */
    private $p825_repository;

    /** @var P825MainService */
    private $p825_service;

    /** @var P825ExportService */
    private $export_service;

    use StepByStep, FileDownloadAble;

    public function __construct(P825RepositoryInterface $p825_repository,P825MainService $p825_service,P825ExportService $export_service)
    {
        $this->p825_repository = $p825_repository;
        $this->p825_service = $p825_service;
        $this->export_service = $export_service;
    }

    public function index(SearchRequest $request) 
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->p825_service->getAll($request->all(),'index'));
    }

    public function exportP825(SearchRequest $request)
    {
        $request->throwJsonIfFailed();
        $fods_file_content = $this->export_service->generateFile($request->all());
        $base_filename = sprintf('P825_report_%s_%s', date('d-m-Y'), time());
            
        $filename_fods = "$base_filename.fods";

        Storage::disk('local')->put('files/P825/'.$filename_fods,$fods_file_content);
        // return Storage::download(sprintf('files/P825/%s',$filename_fods));
        
        if (file_exists(storage_path().'/app/files/P825/'.$filename_fods)) {
            $process = new \Symfony\Component\Process\Process(['/usr/bin/soffice', '--headless', '--convert-to', 'xlsx', storage_path().'/app/files/P825/'.$filename_fods, '--outdir', base_path().'/storage/app/files/P825/']);
            $process->setTimeout(null);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            Storage::disk('local')->delete('files/P825/'.$filename_fods);
            $filename_xlsx = "$base_filename.xlsx";
            return Storage::download(sprintf('files/P825/%s',$filename_xlsx));
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