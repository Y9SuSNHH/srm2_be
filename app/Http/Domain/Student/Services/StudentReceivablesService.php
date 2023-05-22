<?php

namespace App\Http\Domain\Student\Services;

use App\Http\Enum\StudentReceivablePurpose;
use ReflectionException;

class StudentReceivablesService
{
    /**
     * @param $student_receivables
     * @return array
     * @throws ReflectionException
     */
    public function mappingPurpose($student_receivables): array
    {
        $purposes = [];
        foreach ($student_receivables as $student_receivable) {
            $learning_wave_number = $student_receivable->learning_wave_number ?? 0;
            $purpose              = $student_receivable->purpose;

            $purposes[$purpose][$learning_wave_number] = [
                'id'                 => $student_receivable->id,
                'purpose'            => $purpose,
                'purposeName'        => StudentReceivablePurpose::from($purpose)->getLang(),
                'receivable'         => $student_receivable->receivable,
                'learningWaveNumber' => $student_receivable->learning_wave_number,
                'note'               => $student_receivable->note ?? '',
            ];
        }
        return $purposes;
    }

    /**
     * @param $amounts_received
     * @param $student_receivables
     * @return array
     */
    public function mappingAmountsReceivedToPurpose($amounts_received, $student_receivables): array
    {
        $return = [];
        foreach ($amounts_received as $amount_received) {
            $learning_wave_number = $amount_received->dot_hoc_so ?? 0;
            $purpose              = StudentReceivablePurpose::getValueByKeyVi($amount_received->muc_dich_thu);
            $received             = $amount_received->thuc_nop ?? 0;
            if (array_key_exists($purpose, $student_receivables) && array_key_exists($learning_wave_number, $student_receivables[$purpose])) {
                if (!array_key_exists('amountReceived', $student_receivables[$purpose][$learning_wave_number])) {
                    $student_receivables[$purpose][$learning_wave_number]['amountReceived'] = [];
                }
                if (!array_key_exists('totalReceived', $student_receivables[$purpose][$learning_wave_number])) {
                    $student_receivables[$purpose][$learning_wave_number]['totalReceived'] = 0;
                }
                $student_receivables[$purpose][$learning_wave_number]['amountReceived'][] = [
                    'receiptNumber' => $amount_received->so_chung_tu_bien_lai,
                    'receiptDate'   => $amount_received->ngay_bien_lai,
                    'received'       => $received,
                ];
                $student_receivables[$purpose][$learning_wave_number]['totalReceived']    += $received;
            }
        }
        foreach ($student_receivables as $key => $student_receivable) {
            foreach ($student_receivable as $key2 => $each) {
                if (!array_key_exists('amountReceived', $each) || !array_key_exists('totalReceived', $each)) {
                    $student_receivable[$key2]['amountReceived'] = [];
                    $student_receivable[$key2]['totalReceived']  = 0;
                    continue;
                }
                $diff = (int)($each['totalReceived'] - $each['receivable']);
                if (array_key_exists($key2 - 1, $student_receivable)) {
                    $diff = $diff + $student_receivable[$key2 - 1]['diff'];
                }
                $student_receivable[$key2]['diff'] = $diff;

                $return[] = $student_receivable[$key2];
            }
        }
        return $return;
    }
}