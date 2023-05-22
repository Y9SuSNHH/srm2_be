<?php

namespace App\Helpers\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;

trait FileDownloadAble
{
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
