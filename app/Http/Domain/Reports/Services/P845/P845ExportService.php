<?php

namespace App\Http\Domain\Reports\Services\P845;

use App\Http\Domain\Reports\Repositories\P845\P845RepositoryInterface;
use App\Http\Domain\Reports\Services\P845\P845MainService;
use App\Http\Domain\Reports\Services\XmlGenerator;

class P845ExportService
{
    /** @var \App\Http\Domain\Reports\Repositories\P845\P845Repository */
    private $p845_repository;

    private $p845_main_service;
    
    public function __construct(P845RepositoryInterface $p845_repository,P845MainService $p845_main_service)
    {
        $this->p845_repository = $p845_repository;
        $this->p845_main_service = $p845_main_service;
    }

    public function generateFile($request) {
        [ $sheet_1_rows,$sheet_2_rows,$sheet_3_rows ] = $this->p845_main_service->getAll($request,'export');
        $xml_generator = new XmlGenerator;
        $file_content = file_get_contents(storage_path(sprintf('app/template/%s','template_p845.fods')));

        $file_content .= '
        <office:body>
        <office:spreadsheet>
         <table:calculation-settings table:automatic-find-labels="false" table:use-regular-expressions="false" table:use-wildcards="true"/>
         <table:table table:name="P845A" table:style-name="ta1">
          <table:table-column table:style-name="co1" table:number-columns-repeated="47" table:default-cell-style-name="Default"/>
          <table:table-row table:style-name="ro1">
           <table:table-cell table:style-name="ce2" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>STT</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="16" table:number-rows-spanned="1">
            <text:p>Kế hoạch</text:p>
           </table:table-cell>
           <table:covered-table-cell table:number-columns-repeated="15" table:style-name="ce6"/>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="3" table:number-rows-spanned="1">
            <text:p>Thực đạt</text:p>
           </table:table-cell>
           <table:covered-table-cell table:number-columns-repeated="2" table:style-name="ce6"/>
           <table:table-cell table:style-name="ce2" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="3" table:number-rows-spanned="1">
            <text:p>Tuần 0(Trước ngày bắt đầu thu)</text:p>
           </table:table-cell>
           <table:covered-table-cell table:number-columns-repeated="2" table:style-name="ce2"/>
           <table:table-cell table:style-name="ce2" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="4" table:number-rows-spanned="1">
            <text:p>Tuần 1 : Tính từ ngày bắt đầu thu + 7 ngày</text:p>
           </table:table-cell>
           <table:covered-table-cell table:number-columns-repeated="3" table:style-name="ce2"/>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="4" table:number-rows-spanned="1">
            <text:p>Tuần 2 : Kết thúc thu tuần 1 + 7 ngày</text:p>
           </table:table-cell>
           <table:covered-table-cell table:number-columns-repeated="3" table:style-name="ce6"/>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="4" table:number-rows-spanned="1">
            <text:p>Tuần 3 : Kết thúc thu tuần 2 + 7 ngày</text:p>
           </table:table-cell>
           <table:covered-table-cell table:number-columns-repeated="3" table:style-name="ce6"/>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="4" table:number-rows-spanned="1">
            <text:p>Tuần 4 : Kết thúc thu tuần 3 + 7 ngày</text:p>
           </table:table-cell>
           <table:covered-table-cell table:number-columns-repeated="3" table:style-name="ce6"/>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="4" table:number-rows-spanned="1">
            <text:p>Tuần 5 : Kết thúc thu tuần 4 + 7 ngày</text:p>
           </table:table-cell>
           <table:covered-table-cell table:number-columns-repeated="3" table:style-name="ce6"/>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="4" table:number-rows-spanned="1">
            <text:p>Tuần 6 : Kết thúc thu tuần 5 + 7 ngày</text:p>
           </table:table-cell>
           <table:covered-table-cell table:number-columns-repeated="3" table:style-name="ce6"/>
          </table:table-row>
          <table:table-row table:style-name="ro2">
           <table:covered-table-cell table:style-name="ce2"/>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>QLHT</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Lớp</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Ngành</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Đối tượng</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Kỳ thu</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Trạng thái thu</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Ngày đầu kỳ</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Ngày Bắt đầu thu HỌC PHÍ</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Deadline HỌC PHÍ</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>SL SV đang học</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Mục tiêu kỳ</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>SL Phải thu</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Tín chỉ</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Đơn giá</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Doanh thu dự kiến theo kế hoạch</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Số lượng phải thu theo mục tiêu</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Số món thu tích lũy</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Chênh lệch số món với mục tiêu</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Số tiền thu tích lũy</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Số món</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
            <text:p>Thực đạt</text:p>
           </table:table-cell>
           <table:covered-table-cell table:style-name="ce6"/>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Mục tiêu</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Số món</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
            <text:p>Thực đạt</text:p>
           </table:table-cell>
           <table:covered-table-cell table:style-name="ce6"/>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Mục tiêu</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Số món</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
            <text:p>Thực đạt</text:p>
           </table:table-cell>
           <table:covered-table-cell table:style-name="ce6"/>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Mục tiêu</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Số món</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
            <text:p>Thực đạt</text:p>
           </table:table-cell>
           <table:covered-table-cell table:style-name="ce6"/>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Mục tiêu</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Số món</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
            <text:p>Thực đạt</text:p>
           </table:table-cell>
           <table:covered-table-cell table:style-name="ce6"/>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Mục tiêu</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Số món</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
            <text:p>Thực đạt</text:p>
           </table:table-cell>
           <table:covered-table-cell table:style-name="ce6"/>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Mục tiêu</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p>Số món</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
            <text:p>Thực đạt</text:p>
           </table:table-cell>
           <table:covered-table-cell table:style-name="ce6"/>
          </table:table-row>
        ';

        $i = 0;
        for($i = 1;$i <=3;$i++)
        {
            $j = 0;
            ${"sheet_".$i."_content"} = [];
            foreach(${"sheet_".$i."_rows"} as $row)
            {
                $tag = [
                    'name' => 'table:table-row',
                    'attributes' => [
                        ['name' => 'table:style-name', 'value' => 'ro3' ],
                    ],
                ];

                $children = array_values(array_map(function($child) {
                    return [
                        'name' => 'table:table-cell',
                        'attributes' => [
                            ['name' => 'table:style-name' , 'value' => 'ce3'],
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
                },$row));

                $tag['children'] = $children;
                $index = [
                    'name' => 'table:table-cell',
                    'attributes' => [
                        ['name' => 'table:style-name' , 'value' => 'ce3'],
                        ['name' => 'office:value-type' , 'value' => 'string'],
                        ['name' => 'office:value','value' => $j],
                    ],
                    'children' => [
                        [
                            'name' => 'text:p',
                            'value' => $j,
                        ]
                    ]
                ];
                array_unshift($tag['children'],$index);
                array_push(${"sheet_".$i."_content"},$tag);
                $j++;
            }
        }

        $file_content .= $xml_generator->generateXMLTags($sheet_1_content).'
        </table:table>
        <table:table table:name="P845B" table:style-name="ta1">
         <table:table-column table:style-name="co1" table:number-columns-repeated="28" table:default-cell-style-name="Default"/>
         <table:table-row table:style-name="ro1">
          <table:table-cell table:style-name="ce2" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
           <text:p>STT</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="16" table:number-rows-spanned="1">
           <text:p>Kế hoạch</text:p>
          </table:table-cell>
          <table:covered-table-cell table:number-columns-repeated="15" table:style-name="ce6"/>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="3" table:number-rows-spanned="1">
           <text:p>Thực đạt</text:p>
          </table:table-cell>
          <table:covered-table-cell table:number-columns-repeated="2" table:style-name="ce6"/>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="4" table:number-rows-spanned="1">
           <text:p>Tuần 7 : Kết thúc thu tuần 6 + 7 ngày</text:p>
          </table:table-cell>
          <table:covered-table-cell table:number-columns-repeated="3" table:style-name="ce6"/>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="4" table:number-rows-spanned="1">
           <text:p>Tuần 8 : Kết thúc thu tuần 7 + 7 ngày</text:p>
          </table:table-cell>
          <table:covered-table-cell table:number-columns-repeated="3" table:style-name="ce6"/>
         </table:table-row>
         <table:table-row table:style-name="ro2">
          <table:covered-table-cell table:style-name="ce2"/>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>QLHT</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Lớp</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Ngành</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Đối tượng</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Kỳ thu</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Trạng thái thu</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Ngày đầu kỳ</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Ngày Bắt đầu thu HỌC PHÍ</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Deadline HỌC PHÍ</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>SL SV đang học</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Mục tiêu kỳ</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>SL Phải thu</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Tín chỉ</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Đơn giá</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Doanh thu dự kiến theo kế hoạch</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Số lượng phải thu theo mục tiêu</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Số món thu tích lũy</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Chênh lệch số món với mục tiêu</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Số tiền thu tích lũy</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Mục tiêu</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Số món</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
           <text:p>Thực đạt</text:p>
          </table:table-cell>
          <table:covered-table-cell table:style-name="ce6"/>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Mục tiêu</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Số món</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
           <text:p>Thực đạt</text:p>
          </table:table-cell>
          <table:covered-table-cell table:style-name="ce6"/>
         </table:table-row>
        '.$xml_generator->generateXMLTags($sheet_2_content).'
        </table:table>
        <table:table table:name="P845C" table:style-name="ta1">
         <table:table-column table:style-name="co1" table:number-columns-repeated="20" table:default-cell-style-name="Default"/>
         <table:table-row table:style-name="ro1">
          <table:table-cell table:style-name="ce2" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
           <text:p>STT</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="16" table:number-rows-spanned="1">
           <text:p>Kế hoạch</text:p>
          </table:table-cell>
          <table:covered-table-cell table:number-columns-repeated="15" table:style-name="ce6"/>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="3" table:number-rows-spanned="1">
           <text:p>Thực đạt</text:p>
          </table:table-cell>
          <table:covered-table-cell table:number-columns-repeated="2" table:style-name="ce6"/>
         </table:table-row>
         <table:table-row table:style-name="ro2">
          <table:covered-table-cell table:style-name="ce2"/>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>QLHT</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Lớp</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Ngành</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Đối tượng</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Kỳ thu</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Trạng thái thu</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Ngày đầu kỳ</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Ngày Bắt đầu thu HỌC PHÍ</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Deadline HỌC PHÍ</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>SL SV đang học</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Mục tiêu kỳ</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>SL Phải thu</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Tín chỉ</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Đơn giá</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Doanh thu dự kiến theo kế hoạch</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Số lượng phải thu theo mục tiêu</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Số món thu tích lũy</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Chênh lệch số món với mục tiêu</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
           <text:p>Số tiền thu tích lũy</text:p>
          </table:table-cell>
         </table:table-row>
        '.$xml_generator->generateXMLTags($sheet_3_content).'
                        </table:table>
                        <table:named-expressions/>
                    </office:spreadsheet>
                </office:body>
            </office:document>
        ';
        // dd($file_content);
        return $file_content;
    }
}