<?php

namespace App\Http\Domain\Finance\Services;

use App\Http\Domain\Finance\Repositories\Finance\FinanceRepositoryInterface;
use App\Http\Domain\Finance\Requests\Finance\SearchRequest;
use Carbon\Carbon;
use App\Http\Enum\StudentReceivablePurpose;
use App\Helpers\CsvParser;
use App\Http\Domain\Finance\Requests\Finance\TuitionRequest;
use App\Eloquent\StudyPlan;
use App\Http\Domain\Reports\Services\XmlGenerator;

class FinanceService
{
  /**
   * finance_repository
   *
   * @var mixed
   */
  private $finance_repository;
  /**
   * __construct
   *
   * @param  mixed $finance
   * @return void
   */
  public function __construct(FinanceRepositoryInterface $finance)
  {
    $this->finance_repository = $finance;
  }

  public static function exportLabels(): array
  {
    return [
      ['A' => 'DANH SÁCH THU THEO LỚP'],
      [],
      [
        'A' => 'Ngày xuất danh sách',
        'B' => Carbon::now()->format('d/m/Y'),
      ],
      [],
      [
        'A'  => 'STT',
        'B'  => 'Ngày bắt đầu thu',
        'C'  => 'Ngành',
        'D'  => 'Mã lớp',
        'E'  => 'Đợt học',
        'F'  => 'SLSV',
        'G'  => 'QLHT',
      ],
    ];
  }

  public function export(SearchRequest $request): array
  {
    $data = self::exportLabels();
    $finances = $this->finance_repository->getAllByClass($request);

    foreach ($finances as $key => $finance) {
      $data[] = [
        'A'  => $key + 1,
        'B'  => $finance->day_begin ?? null,
        'C'  => $finance->major ?? null,
        'D'  => $finance->class ?? null,
        'E'  => $finance->semester ?? null,
        'F'  => $finance->number_student ?? null,
        'G'  => $finance->qlht ?? null,
      ];
    }

    $temp_file = CsvParser::createCsvUTF8BOMTmp($data);

    return $temp_file ? stream_get_meta_data($temp_file) : [];
  }

  public static function labelTransaction(): array
  {
    return [
      ['A' => 'DANH SÁCH THU THEO SINH VIÊN'],
      [],
      [
        'A' => 'Ngày xuất danh sách',
        'B' => Carbon::now()->format('d/m/Y'),
      ],
      [],
      [
        'A'  => 'STT',
        'B'  => 'Trạm',
        'C'  => 'Mã lớp',
        'D'  => 'Đợt',
        'E'  => 'Mã hồ sơ',
        'F'  => 'Mã sinh viên',
        'G'  => 'Họ và tên',
        'H'  => 'Ngày sinh',
        'I'  => 'Quản lý học tập',
        'J'  => 'Mục đích thu',
        'K'  => 'Số phải thu',
        'L'  => 'Ghi chú',
      ],
    ];
  }

  public function exportTransaction(SearchRequest $request): array
  {
    $data = self::labelTransaction();
    $finances = $this->finance_repository->getAllByStudent($request);

    foreach ($finances as $key => $finance) {
      $data[] = [
        'A'  => $key + 1,
        'B'  => $finance->student->classroom->area->code ?? null,
        'C'  => $finance->student->classroom->code ?? null,
        'D'  => $finance->no ?? null,
        'E'  => $finance->studentProfile->profile_code ?? null,
        'F'  => $finance->student->student_code ?? null,
        'G'  => ($finance->studentProfile->getProfile->firstname ?? null) . ' ' . ($finance->studentProfile->getProfile->lastname ?? null),
        'H'  => $finance->studentProfile->getProfile->birthday ?? null,
        'I'  => $finance->student->classroom->staff->fullname ?? null,
        'J'  => StudentReceivablePurpose::getValueByKey($finance->purpose),
        'K'  => $finance->amount ?? null,
        'L'  => $finance->note ?? null,
      ];
    }

    $temp_file = CsvParser::createCsvUTF8BOMTmp($data);

    return $temp_file ? stream_get_meta_data($temp_file) : [];
  }

  public static function labelTuition(): array
  {
    return [
      ['A' => 'DANH SÁCH HỌC PHÍ THEO KỲ'],
      [],
      [
        'A' => 'Ngày xuất danh sách',
        'B' => Carbon::now()->format('d/m/Y'),
      ],
      [],
      [
        'A'  => 'STT',
        'B'  => 'Trạm',
        'C'  => 'Mã lớp',
        'D'  => 'Đợt',
        'E'  => 'Mã hồ sơ',
        'F'  => 'Mã sinh viên',
        'G'  => 'Họ và tên',
        'H'  => 'Ngày sinh',
        'I'  => 'Quản lý học tập',
        'J'  => 'Số phải thu',
        'K'  => 'Thực thu',
        'L'  => 'Tổng thực thu',
        'M'  => 'Chênh lệch',
        'N'  => 'Số chứng từ biên lai',
        'O'  => 'Ngày xuất biên lai',
        'P'  => 'Ghi chú',
      ],
    ];
  }

  public function exportTuition(TuitionRequest $request): array
  {
    $data = self::labelTuition();
    $finances = $this->finance_repository->getTuition($request);

    foreach ($finances as $key => $finance) {
      $thuc_thu = '';
      $so_chung_tu_bien_lai = '';
      $ngay_xuat_bien_lai = '';
      if (count($finance->amountsReceived) > 0) {
        foreach ($finance->amountsReceived as $item => $received) {
          if ($item == 0) {
            $thuc_thu = $received->thuc_thu;
            $so_chung_tu_bien_lai = $received->so_chung_tu_bien_lai;
            $ngay_xuat_bien_lai = $received->ngay_xuat_bien_lai;
          } else {
            $thuc_thu = $thuc_thu . PHP_EOL . $received->thuc_thu;
            $so_chung_tu_bien_lai = $so_chung_tu_bien_lai . PHP_EOL . $received->so_chung_tu_bien_lai;
            $ngay_xuat_bien_lai = $ngay_xuat_bien_lai . PHP_EOL . $received->ngay_xuat_bien_lai;
          }
        }
      };
      $data[] = [
        'A'  => $key + 1,
        'B'  => $finance->student->classroom->area->code ?? null,
        'C'  => $finance->student->classroom->code ?? null,
        'D'  => $finance->no ?? null,
        'E'  => $finance->studentProfile->profile_code ?? null,
        'F'  => $finance->student->student_code ?? null,
        'G'  => ($finance->studentProfile->getProfile->firstname ?? null) . ' ' . ($finance->studentProfile->getProfile->lastname ?? null),
        'H'  => $finance->studentProfile->getProfile->birthday ?? null,
        'I'  => $finance->student->classroom->staff->fullname ?? null,
        'J'  => $finance->amount ?? null,
        'K'  => $thuc_thu ?? null,
        'L'  => (count($finance->totalReceived) > 0) ? $finance->totalReceived[0]->total_thuc_nop : null,
        'M'  => ((count($finance->totalReceived) > 0) ? $finance->totalReceived[0]->total_thuc_nop : 0) - $finance->amount ?? null,
        'N'  => $so_chung_tu_bien_lai ?? null,
        'O'  => $ngay_xuat_bien_lai ?? null,
        'P'  => $finance->note ?? null,
      ];
    }

    $temp_file = CsvParser::createCsvUTF8BOMTmp($data);

    return $temp_file ? stream_get_meta_data($temp_file) : [];
  }

  public function generateFile(SearchRequest $request)
  {
    $finance = $this->finance_repository->getDataExport($request);

    $xml_generator = new XmlGenerator;

    $semester = $request->semester;
    $classId = $request->classId;

    $year = $this->finance_repository->getYearOfPeriods($classId, $semester);
    $classroom = $this->finance_repository->getCodeClassroom($classId);
    $studyPlans = $this->finance_repository->getStudyPlans($classId, $semester);

    $subjectName = '';
    $amountCredit = '';
    foreach ($studyPlans as $studyPlan) {
      $subjectName .= '    
      <table:table-cell table:style-name="ce18" office:value-type="string" calcext:value-type="string">
        <text:p>' . $studyPlan->subjectName . '</text:p>
      </table:table-cell>';

      $amountCredit .= '    
      <table:table-cell table:style-name="ce19" office:value-type="string" office:value="'.$studyPlan->amount_credit.'" calcext:value-type="string">
        <text:p>'.$studyPlan->amount_credit.'</text:p>
      </table:table-cell>
      ';
    }

    // dd($subjectName);
    $count = count($studyPlans);
    $file_content = file_get_contents(storage_path(sprintf('app/template/%s', 'template_tuition.fods')));

    $file_content .= '    
    <table:table-row table:style-name="ro1">
      <table:table-cell table:style-name="ce1" table:number-columns-repeated="5"/>
      <table:table-cell table:style-name="ce14"/>
      <table:table-cell table:style-name="ce1" table:number-columns-repeated="3"/>
      <table:table-cell table:style-name="ce14"/>
      <table:table-cell table:style-name="ce1"/>
      <table:table-cell table:style-name="ce14" table:number-columns-repeated="2"/>
      <table:table-cell table:style-name="ce27" table:number-columns-repeated="4"/>
      <table:table-cell table:style-name="ce1"/>
      <table:table-cell table:number-columns-repeated="16366"/>
    </table:table-row>

    <table:table-row table:style-name="ro2">
    <table:table-cell table:style-name="ce2" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="'.(12 + $count).'" table:number-rows-spanned="1">
     <text:p>DANH SÁCH PHẢI THU HỌC PHÍ LỚP ' . $classroom . '</text:p>
    </table:table-cell>
    <table:covered-table-cell table:number-columns-repeated="'.(11 + $count).'" table:style-name="ce6"/>
    <table:table-cell table:number-columns-repeated="16366"/>
   </table:table-row>

   <table:table-row table:style-name="ro2">
    <table:table-cell table:style-name="ce2" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="'.(12 + $count).'" table:number-rows-spanned="1">
     <text:p>Đợt ' . $semester . ' - NĂM ' . $year . '</text:p>
    </table:table-cell>
    <table:covered-table-cell table:number-columns-repeated="'.(11 + $count).'" table:style-name="ce6"/>
    <table:table-cell table:number-columns-repeated="16366"/>
   </table:table-row>

   <table:table-row table:style-name="ro1">
    <table:table-cell table:style-name="ce3"/>
    <table:table-cell table:style-name="ce7"/>
    <table:table-cell table:style-name="ce3"/>
    <table:table-cell table:style-name="ce7" table:number-columns-repeated="'.(9 + $count).'"/>
    <table:table-cell table:style-name="ce3"/>
    <table:table-cell table:number-columns-repeated="16365"/>
    </table:table-row>

    <table:table-row table:style-name="ro3">
     <table:table-cell table:style-name="ce4" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="3">
      <text:p>STT</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce4" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="3">
      <text:p>Mã học viên</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce4" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="3">
      <text:p>Họ đệm</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce4" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="3">
      <text:p>Tên</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce4" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="3">
      <text:p>Ngày sinh</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce4" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="3">
      <text:p>Nơi sinh</text:p>
     </table:table-cell>
     <table:table-cell table:style-name="ce15" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="3"><text:p>Đối </text:p><text:p>tượng</text:p>
     </table:table-cell>
    <table:table-cell table:style-name="ce17" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="' . $count . '" table:number-rows-spanned="1">
     <text:p>Môn học Đợt học số ' . $semester . '</text:p>
    </table:table-cell>
    <table:covered-table-cell table:number-columns-repeated="'.($count-1).'" table:style-name="ce22"/>
    <table:table-cell table:style-name="ce4" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="3">
      <text:p>Tổng số tín chỉ</text:p>
    </table:table-cell>
    <table:table-cell table:style-name="ce4" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="3">
      <text:p>Hoc phí/ tín chỉ</text:p>
    </table:table-cell>
    <table:table-cell table:style-name="ce4" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="3">
      <text:p>Học phí</text:p>
    </table:table-cell>
    <table:table-cell table:style-name="ce4" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="3">
      <text:p>Chênh lệch </text:p>
    </table:table-cell>
    <table:table-cell table:style-name="ce32" office:value-type="string" office:string-value="Thực thu" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="3">
      <text:p><text:s/>Thực thu </text:p>
    </table:table-cell>
    <table:table-cell table:style-name="ce4" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="3">
      <text:p>Ghi chú</text:p>
    </table:table-cell>
    <table:table-cell table:number-columns-repeated="16365"/>
    </table:table-row>

    <table:table-row table:style-name="ro4">
      <table:covered-table-cell table:number-columns-repeated="'.( ($count >6) ? $count : 6 ).'" table:style-name="ce4"/>
      <table:covered-table-cell table:style-name="ce15"/>'.
      $subjectName
      .'<table:covered-table-cell table:number-columns-repeated="'.( ($count >6) ? $count : 6 ).'" table:style-name="ce4"/>
      <table:table-cell table:number-columns-repeated="16365"/>
    </table:table-row>

    <table:table-row table:style-name="ro5">
    <table:covered-table-cell table:number-columns-repeated="'.( ($count >6) ? $count : 6 ).'" table:style-name="ce4"/>
    <table:covered-table-cell table:style-name="ce15"/>'. 
      $amountCredit 
      .'
      <table:covered-table-cell table:number-columns-repeated="'.( ($count >6) ? $count : 6 ).'" table:style-name="ce4"/>
      <table:table-cell table:number-columns-repeated="16365"/>
    </table:table-row>';

   $content = [];
        $i = 1;
        foreach($finance as $data)
        {
            $tag = [
                'name' => 'table:table-row',
                'attributes' => [
                    ['name' => 'table:style-name', 'value' => 'ro6' ],
                ],
            ];

            $children = array_values(array_map(function($child) {
                return [
                    'name' => 'table:table-cell',
                    'attributes' => [
                        ['name' => 'table:style-name' , 'value' => 'ce8'],
                        ['name' => 'office:value-type' , 'value' => 'string'],
                        ['name' => 'office:value','value' => htmlspecialchars($child,ENT_QUOTES,'UTF-8')],
                    ],
                    'children' => [
                        [
                            'name' => 'text:p',
                            'value' => $child,
                        ]
                    ]
                ];
            },$data));

            $tag['children'] = $children;
            $index = [
                'name' => 'table:table-cell',
                'attributes' => [
                    ['name' => 'table:style-name' , 'value' => 'ce8'],
                    ['name' => 'office:value-type' , 'value' => 'string'],
                    ['name' => 'office:value','value' => $i],
                ],
                'children' => [
                    [
                        'name' => 'text:p',
                        'value' => $i,
                    ]
                ]
            ];
            array_unshift($tag['children'],$index);
            array_push($content,$tag);
            $i++;
        }

        $file_content .= $xml_generator->generateXMLTags($content);

    $file_content .= '
    <table:named-expressions>
     <table:named-range table:name="_xlnm.Print_Area" table:base-cell-address="$&apos;Học phí&apos;.$A$1" table:cell-range-address="$&apos;Học phí&apos;.$A$1:.$S$11" table:range-usable-as="print-range"/>
    </table:named-expressions>
    <calcext:conditional-formats>
     <calcext:conditional-format calcext:target-range-address="&apos;Học phí&apos;.K6:&apos;Học phí&apos;.M6">
      <calcext:condition calcext:apply-style-name="ConditionalStyle_1" calcext:value="duplicate" calcext:base-cell-address="&apos;Học phí&apos;.K6"/>
     </calcext:conditional-format>
     <calcext:conditional-format calcext:target-range-address="&apos;Học phí&apos;.H6:&apos;Học phí&apos;.J6">
      <calcext:condition calcext:apply-style-name="ConditionalStyle_2" calcext:value="duplicate" calcext:base-cell-address="&apos;Học phí&apos;.H6"/>
     </calcext:conditional-format>
    </calcext:conditional-formats>
   </table:table>
   <table:named-expressions/>
   <table:database-ranges>
    <table:database-range table:name="__Anonymous_Sheet_DB__0" table:target-range-address="&apos;Học phí&apos;.A8:&apos;Học phí&apos;.S12"/>
   </table:database-ranges>
  </office:spreadsheet>
 </office:body>
</office:document>';
    return $file_content;
  }
}
