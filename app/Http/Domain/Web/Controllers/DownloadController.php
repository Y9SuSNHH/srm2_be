<?php

namespace App\Http\Domain\Web\Controllers;

use App\Http\Domain\TrainingProgramme\Services\PeriodService;
use Illuminate\Http\Exceptions\HttpResponseException;

class DownloadController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * @param PeriodService $service
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function studySessionTemplate(PeriodService $service): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $meta = $service->createTemplateFile();

        return $this->createDownloadCsvUTF8BOM($meta, "Dot_hoc.csv");
    }

    /**
     * Execute Download Csv UTF8-BOM
     *
     * @param array $meta
     * @param string $file_name
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    private function createDownloadCsvUTF8BOM(array $meta, string $file_name): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        try {
            $file = $meta['uri'];
            // download
            $headers = [
                'Content-Encoding: UTF-8',
                'Content-Type' => 'text/csv',
            ];
            return response()->download($file, $file_name, $headers);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => ['file not found', $e->getMessage()]], 404));
        }
    }
}
