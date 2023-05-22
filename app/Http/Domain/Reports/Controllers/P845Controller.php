<?php

namespace App\Http\Domain\Reports\Controllers;



use App\Http\Domain\Reports\Repositories\P845\P845Repository;
use App\Http\Domain\Reports\Repositories\P845\P845RepositoryInterface;
use App\Http\Domain\Reports\Requests\P845\SearchRequest;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Traits\FileDownloadAble;
use App\Helpers\Traits\StepByStep;
use App\Http\Domain\Reports\Services\P845\P845ExportService;
use App\Http\Domain\Reports\Services\P845\P845MainService;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Laravel\Lumen\Routing\Controller;

/**
 * Class P845Controller
 * @package App\Http\Domain\Reports\Controllers
 */
class P845Controller extends Controller
{
    /** @var P845RepositoryInterface */
    private $p845_repository;

    /** @var P845MainService */
    private $p845_service;

    /** @var P845ExportService */
    private $export_service;

    use StepByStep, FileDownloadAble;

    public function __construct(P845RepositoryInterface $p845_repository,P845MainService $p845_service,P845ExportService $export_service)
    {
        $this->p845_repository = $p845_repository;
        $this->p845_service = $p845_service;
        $this->export_service = $export_service;
    }

    public function index(SearchRequest $request) 
    {
        $request->throwJsonIfFailed();
        return json_response(true, $this->p845_service->getAll($request->all(),'index'));
    }

    public function exportP845(SearchRequest $request)
    {
        $request->throwJsonIfFailed();
        $fods_file_content = $this->export_service->generateFile($request->all());
        $base_filename = sprintf('Baocaodoanhthuhocphi_%s_%s', date('d-m-Y'), time());
            
        $filename_fods = "$base_filename.fods";

        Storage::disk('local')->put('files/P845/'.$filename_fods,$fods_file_content);
        // return Storage::download(sprintf('files/P845/%s',$filename_fods));
        
        if (file_exists(storage_path().'/app/files/P845/'.$filename_fods)) {
            $process = new \Symfony\Component\Process\Process(['/usr/bin/soffice', '--headless', '--convert-to', 'xlsx', storage_path().'/app/files/P845/'.$filename_fods, '--outdir', base_path().'/storage/app/files/P845/']);
            $process->setTimeout(null);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            Storage::disk('local')->delete('files/P845/'.$filename_fods);
            $filename_xlsx = "$base_filename.xlsx";
            return Storage::download(sprintf('files/P845/%s',$filename_xlsx));
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