<?php

namespace App\Http\Domain\Reports\Controllers;



use App\Http\Domain\Reports\Repositories\G120\G120Repository;
use App\Http\Domain\Reports\Repositories\G120\G120RepositoryInterface;
use App\Http\Domain\Reports\Requests\G120\SearchRequest;
use App\Http\Domain\Reports\Requests\G120\ManageEngagementRequest;
use App\Http\Domain\Reports\Services\G120\ManageEngagementProcessesService;
use App\Http\Domain\Reports\Services\G120\G120ExportFileService;
use App\Http\Enum\LockDay;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Traits\FileDownloadAble;
use App\Helpers\Traits\StepByStep;
use App\Providers\AuthManager;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Laravel\Lumen\Routing\Controller;

/**
 * Class G120Controller
 * @package App\Http\Domain\Reports\Controllers
 */
class G120Controller extends Controller
{
    private $g120_repository;

    /** @var ManageEngagementProcessesService */
    private $manage_service;

    /** @var G120ExportFile */
    private $export_service;

    use StepByStep, FileDownloadAble;

    public function __construct(G120RepositoryInterface $g120_repository)
    {
        $this->g120_repository = $g120_repository;
        $this->manage_service = new ManageEngagementProcessesService($this->g120_repository);
        $this->export_service = new G120ExportFileService($this->g120_repository,$this->manage_service);
    }
    
    public function index(SearchRequest $request) 
    {
        $request->throwJsonIfFailed();
        $query_data = $this->g120_repository->getAll($request->all());
        $first_day_of_school = $request->first_day_of_school;
        return json_response(true, [
            'data' => $this->manage_service->addRevenue($query_data),
            'disabled' => [
                'week1' => LockDay::isLockWeek1Rating($first_day_of_school !== '' ? Carbon::createFromFormat('Y-m-d',$request->all()['first_day_of_school']) : null),
                'week4' => LockDay::isLockWeek4Rating($first_day_of_school !== '' ? Carbon::createFromFormat('Y-m-d',$request->all()['first_day_of_school']) : null),
            ]    
        ]);
    }

    public function manageEngagementProcesses(ManageEngagementRequest $request)
    {
        $request->throwJsonIfFailed();
        $execute_update = $this->manage_service->updateQuery($request->all());
        return json_response($execute_update['successful'], $execute_update['message']);
    }
    public function exportG120(SearchRequest $request)
    {
        $request->throwJsonIfFailed();
        $fods_file_content = $this->export_service->generateFile($request->all());
        $base_filename = sprintf('G120_report_%s_%s', date('d-m-Y'), strtotime(date('d-m-Y H:i:s')));
            
        $filename_fods = "$base_filename.fods";

        Storage::disk('local')->put('files/G120/'.$filename_fods,$fods_file_content);
        // return Storage::download(sprintf('G120/%s',$filename_fods));
        
        if (file_exists(storage_path().'/app/files/G120/'.$filename_fods)) {
            $process = new \Symfony\Component\Process\Process(['/usr/bin/soffice', '--headless', '--convert-to', 'xlsx', storage_path().'/app/files/G120/'.$filename_fods, '--outdir', base_path().'/storage/app/files/G120/']);
            $process->setTimeout(null);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            Storage::disk('local')->delete('files/G120/'.$filename_fods);
            $filename_xlsx = "$base_filename.xlsx";
            return Storage::download(sprintf('files/G120/%s',$filename_xlsx));
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