<?php

namespace App\Http\Domain\Reports\Controllers;



use App\Http\Domain\Reports\Repositories\G820\G820Repository;
use App\Http\Domain\Reports\Repositories\G820\G820RepositoryInterface;
use App\Http\Domain\Reports\Requests\G820\SearchRequest;
use App\Http\Domain\Reports\Services\G820\ExecuteData;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Traits\FileDownloadAble;
use App\Helpers\Traits\StepByStep;
use App\Http\Domain\Reports\Services\G820\G820ExportFile;
use App\Providers\AuthManager;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Laravel\Lumen\Routing\Controller;
use App\Http\Enum\StudentStatus;

/**
 * Class G820Controller
 * @package App\Http\Domain\Reports\Controllers
 */
class G820Controller extends Controller
{
    /** @var G820RepositoryInterface */
    private $g820_repository;

    /** @var ExecuteData */
    private $g820_service;

    /** @var G820ExportFile */
    private $export_service;

    use StepByStep, FileDownloadAble;

    public function __construct(G820RepositoryInterface $g820_repository,ExecuteData $g820_service,G820ExportFile $export_service)
    {
        $this->g820_repository = $g820_repository;
        $this->g820_service = $g820_service;
        $this->export_service = $export_service;
    }

    public function index(SearchRequest $request) 
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->g820_service->getAll($request->all(),'index'));
    }

    public function exportG820(SearchRequest $request)
    {
        $request->throwJsonIfFailed();
        $fods_file_content = $this->export_service->generateFile($request->all());
        $base_filename = sprintf('G820_report_%s_%s', date('d-m-Y'), time());
            
        $filename_fods = "$base_filename.fods";

        Storage::disk('local')->put('files/G820/'.$filename_fods,$fods_file_content);
        // return Storage::download(sprintf('files/G820/%s',$filename_fods));
        
        if (file_exists(storage_path().'/app/files/G820/'.$filename_fods)) {
            $process = new \Symfony\Component\Process\Process(['/usr/bin/soffice', '--headless', '--convert-to', 'xlsx', storage_path().'/app/files/G820/'.$filename_fods, '--outdir', base_path().'/storage/app/files/G820/']);
            $process->setTimeout(null);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            Storage::disk('local')->delete('files/G820/'.$filename_fods);
            $filename_xlsx = "$base_filename.xlsx";
            return Storage::download(sprintf('files/G820/%s',$filename_xlsx));
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