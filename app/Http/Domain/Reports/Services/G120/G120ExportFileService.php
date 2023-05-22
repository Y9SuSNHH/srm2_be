<?php

namespace App\Http\Domain\Reports\Services\G120;

use App\Http\Domain\Reports\Repositories\G120\G120RepositoryInterface;
use App\Http\Enum\ProfileStatus;
use App\Http\Domain\Reports\Services\XmlGenerator;
use App\Http\Enum\LockDay;
use App\Http\Enum\ReportStyles;
use Carbon\Carbon;

class G120ExportFileService
{
    /** @var \App\Http\Domain\Reports\Repositories\G120\G120Repository */
    private $g120_repository;

    private $manage_service;
    
    public function __construct(G120RepositoryInterface $g120_repository,ManageEngagementProcessesService $manage_service)
    {
        $this->g120_repository = $g120_repository;
        $this->manage_service = $manage_service;
    }

    public function generateFile($request)
    {
        if($request['classes'] != '')
        {
            $request['classes'] = explode(',', $request['classes']);
        }

        $first_sheet_data = $this->g120_repository->getStudentsForExport($request);
        $first_sheet_rows = $this->manage_service->addRevenue($first_sheet_data)->map(function($data) {
            unset($data['id']);
            return $data;
        });

        $second_sheet_rows = $this->g120_repository->getG120ByClass($request);

        $xml_generator = new XmlGenerator;
        // $file_content = $xml_generator->fodsFileHeader();
        // $style_arr = ReportStyles::G120_STYLES;
        // $styles = $xml_generator->generateXMLTags($style_arr);
        $file_content = file_get_contents(storage_path(sprintf('app/template/%s','template_g120.fods')));
        $first_day_of_school = array_key_exists('first_day_of_school',$request) ? date('d/m/Y',strtotime($request['first_day_of_school'])) : '';
        $content = [];
        $total = [
            'w1' => [
                'A1' => 0,
                'A2' => 0,
                'B1' => 0,
                'B2_HS' => 0,
                'B2_HT' => 0,
                'B3' => 0,
                'C' => 0,
                'total' => 0,
            ],
            'w3' => [
                'A1' => 0,
                'A2' => 0,
                'B1' => 0,
                'B2_HS' => 0,
                'B2_HT' => 0,
                'B3' => 0,
                'C' => 0,
                'total' => 0,
            ],
            'w4' => [
                'A1' => 0,
                'A2' => 0,
                'B1' => 0,
                'B2_HS' => 0,
                'B2_HT' => 0,
                'B3' => 0,
                'C' => 0,
                'total' => 0,
            ],
        ];
        $types = ['A1', 'A2', 'B1', 'B2_HS','B2_HT','B3', 'C','Tổng'];

        $i = 1;
        foreach($first_sheet_rows as $row)
        {
            $tag = [
                'name' => 'table:table-row',
                'attributes' => [
                    ['name' => 'table:style-name', 'value' => 'ro5' ],
                ],
            ];
            $children = array_map(function($child) {
                return [
                    'name' => 'table:table-cell',
                    'attributes' => [
                        ['name' => 'table:style-name' , 'value' => 'ce7'],
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
            },$row);
            
            $tag['children'] = $children;
            $index = [
                'name' => 'table:table-cell',
                'attributes' => [
                    ['name' => 'table:style-name' , 'value' => 'ce7'],
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

            if(!empty($row['student_type_first_week']) && in_array($row['student_type_first_week'],$types))
            {
                $total['w1'][$row['student_type_first_week']]++;
                $total['w1']['total']++;
            }
            
            if(!empty($row['student_type_fourth_week']) && in_array($row['student_type_first_week'],$types))
            {
                $total['w4'][$row['student_type_fourth_week']]++;
                $total['w4']['total']++;
            }
            $i++;
        }

        $file_content .= '
        <office:body>
        <office:spreadsheet>
         <table:calculation-settings table:automatic-find-labels="false" table:use-regular-expressions="false" table:use-wildcards="true"/>
         <table:table table:name="G120 chi tiết" table:style-name="ta1">
          <table:table-column table:style-name="co1" table:default-cell-style-name="Default"/>
          <table:table-column table:style-name="co1" table:default-cell-style-name="ce18"/>
          <table:table-column table:style-name="co1" table:number-columns-repeated="5" table:default-cell-style-name="ce10"/>
          <table:table-column table:style-name="co1" table:default-cell-style-name="ce29"/>
          <table:table-column table:style-name="co1" table:number-columns-repeated="13" table:default-cell-style-name="Default"/>
          <table:table-column table:style-name="co2" table:default-cell-style-name="Default"/>
          <table:table-column table:style-name="co1" table:number-columns-repeated="6" table:default-cell-style-name="Default"/>
          <table:table-row table:style-name="ro1">
           <table:table-cell table:style-name="ce2" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="28" table:number-rows-spanned="1">
            <text:p>G120 - DANH SÁCH SINH VIÊN NHẬP HỌC THEO LỚP</text:p>
           </table:table-cell>
           <table:covered-table-cell table:number-columns-repeated="8" table:style-name="ce9"/>
           <table:covered-table-cell table:number-columns-repeated="19" table:style-name="ce10"/>
          </table:table-row>
          <table:table-row table:style-name="ro2">
           <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="28" table:number-rows-spanned="1">
            <text:p>Ngày khai giảng:'.$first_day_of_school.' </text:p>
           </table:table-cell>
           <table:covered-table-cell table:style-name="ce10"/>
           <table:covered-table-cell table:number-columns-repeated="5"/>
           <table:covered-table-cell table:number-columns-repeated="21" table:style-name="ce10"/>
          </table:table-row>
          <table:table-row table:style-name="ro2">
           <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="28" table:number-rows-spanned="1">
            <text:p>Ngày báo cáo: '.date('d/m/Y').'</text:p>
           </table:table-cell>
           <table:covered-table-cell table:style-name="ce10"/>
           <table:covered-table-cell table:number-columns-repeated="5"/>
           <table:covered-table-cell table:number-columns-repeated="21" table:style-name="ce10"/>
          </table:table-row>
          <table:table-row table:style-name="ro2">
           <table:table-cell table:style-name="ce3"/>
           <table:table-cell table:style-name="ce10"/>
           <table:table-cell table:number-columns-repeated="5"/>
           <table:table-cell table:style-name="ce10" table:number-columns-repeated="21"/>
          </table:table-row>
          <table:table-row table:style-name="ro3">
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p><text:s text:c="2"/>STT</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Khu vực</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Mã hồ sơ</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Mã sinh viên</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Họ và đệm</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Tên</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Ngày sinh</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Giới tính</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Nơi sinh</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Số điện thoại</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Email cá nhân</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Địa chỉ liên hệ</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Tài khoản học tập</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Tên lớp</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Ngành đăng ký</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>TVTS</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>QLHT</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Phải thu học phí kì 1</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Thực thu học phí kỳ 1 (không kèm Lệ phí xét tuyển)</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Chênh lệch = Thực thu - Phải thu</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Trạng thái hồ sơ giáo vụ</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Level</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="3" table:number-rows-spanned="1">
            <text:p>Trạng thái sinh viên</text:p>
           </table:table-cell>
           <table:covered-table-cell table:number-columns-repeated="2" table:style-name="ce5"/>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="3" table:number-rows-spanned="1">
            <text:p>Xếp loại sinh viên nhập học</text:p>
           </table:table-cell>
           <table:covered-table-cell table:number-columns-repeated="2" table:style-name="ce5"/>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Ghi chú</text:p>
           </table:table-cell>
          </table:table-row>
          <table:table-row table:style-name="ro4">
           <table:covered-table-cell table:number-columns-repeated="22" table:style-name="ce5"/>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
            <text:p>Tình trạng tham gia khai giảng</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
            <text:p>Tình trạng tham gia học tập 1</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
            <text:p>Tình trạng học tập tuần 4</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
            <text:p>Tuần 1(chốt về học phí)</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
            <text:p>Tuần 3 (Chốt về học phí + hồ sơ)</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
            <text:p>Tuần 4 (chốt về học phí, hồ sơ, học tập)</text:p>
           </table:table-cell>
           <table:covered-table-cell table:style-name="ce5"/>
          </table:table-row>
        ';

        $file_content .= $xml_generator->generateXMLTags($content).'
                <table:table-row table:style-name="ro6" table:number-rows-repeated="3">
                <table:table-cell/>
                <table:table-cell table:style-name="Default" table:number-columns-repeated="7"/>
                <table:table-cell table:number-columns-repeated="20"/>
            </table:table-row>
            <table:table-row table:style-name="ro3">
                <table:table-cell/>
                <table:table-cell table:style-name="ce13" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
                <text:p>Sinh viên loại</text:p>
                </table:table-cell>
                <table:table-cell table:style-name="ce22" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
                <text:p>Tuần 1</text:p>
                </table:table-cell>
                <table:covered-table-cell table:style-name="ce22"/>
                <table:table-cell table:style-name="ce22" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
                <text:p>Tuần 3</text:p>
                </table:table-cell>
                <table:covered-table-cell table:style-name="ce22"/>
                <table:table-cell table:style-name="ce26" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
                <text:p>Tuần 4</text:p>
                </table:table-cell>
                <table:covered-table-cell table:style-name="ce26"/>
                <table:table-cell table:number-columns-repeated="6"/>
                <table:table-cell table:style-name="ce10"/>
                <table:table-cell table:style-name="ce10" office:value-type="string" calcext:value-type="string">
                <text:p>Tư vấn tuyển sinh</text:p>
                </table:table-cell>
                <table:table-cell table:style-name="ce10" table:number-columns-repeated="2"/>
                <table:table-cell table:style-name="ce10" office:value-type="string" calcext:value-type="string">
                <text:p>Kế toán</text:p>
                </table:table-cell>
                <table:table-cell table:number-columns-repeated="9"/>
            </table:table-row>
            <table:table-row table:style-name="ro3">
                <table:table-cell/>
                <table:covered-table-cell table:style-name="ce15"/>
                <table:table-cell table:style-name="ce23" office:value-type="string" calcext:value-type="string">
                <text:p>Số lượng</text:p>
                </table:table-cell>
                <table:table-cell table:style-name="ce23" office:value-type="string" calcext:value-type="string">
                <text:p>Tỉ lệ %</text:p>
                </table:table-cell>
                <table:table-cell table:style-name="ce23" office:value-type="string" calcext:value-type="string">
                <text:p>Số lượng</text:p>
                </table:table-cell>
                <table:table-cell table:style-name="ce23" office:value-type="string" calcext:value-type="string">
                <text:p>Tỉ lệ %</text:p>
                </table:table-cell>
                <table:table-cell table:style-name="ce23" office:value-type="string" calcext:value-type="string">
                <text:p>Số lượng</text:p>
                </table:table-cell>
                <table:table-cell table:style-name="ce27" office:value-type="string" calcext:value-type="string">
                <text:p>Tỉ lệ %</text:p>
                </table:table-cell>
                <table:table-cell table:number-columns-repeated="20"/>
            </table:table-row>
        ';
        
        $content = [];
        foreach($types as $type) {
            $tag = [
                'name' => 'table:table-row',
                'attributes' => [
                    ['name' => 'table:style-name', 'value' => 'ro3' ],
                ],
            ];
            $children = array_map(function ($w) use ($type){
                return [
                            [
                                'name' => 'table:table-cell',
                                'attributes' => [
                                    ['name' => 'table:style-name' , 'value' => 'ce33'],
                                    ['name' => 'office:value-type' , 'value' => 'string'],
                                    ['name' => 'office:value','value' => strval($w[$type !== 'Tổng' ? $type : 'total'])],
                                ],
                                'children' => [
                                    [
                                        'name' => 'text:p',
                                        'value' => strval($w[$type !== 'Tổng' ? $type : 'total']),
                                    ]
                                ]
                            ],
                            [
                                'name' => 'table:table-cell',
                                'attributes' => [
                                    ['name' => 'table:style-name' , 'value' => 'ce33'],
                                    ['name' => 'office:value-type' , 'value' => 'string'],
                                    ['name' => 'office:value','value' => strval(round($w['total'] != 0 ? (($type !== 'Tổng' ? $w[$type] : $w['total']) / $w['total']) : 0,2))],
                                ],
                                'children' => [
                                    [
                                        'name' => 'text:p',
                                        'value' => strval($w['total'] != 0 ? round(($type !== 'Tổng' ? $w[$type] : $w['total']) / $w['total'] * 100,2) : 0),
                                    ]
                                ]
                            ],
                        ];
            },$total);

            $tag['children'] = array_merge(...array_values($children));
            $type_name_cell = [
                'name' => 'table:table-cell',
                'attributes' => [
                    ['name' => 'table:style-name' , 'value' => 'ce23'],
                    ['name' => 'office:value-type' , 'value' => 'string'],
                    ['name' => 'office:value','value' => $type],
                ],
                'children' => [
                    [
                        'name' => 'text:p',
                        'value' => $type,
                    ]
                ]
            ];
            array_unshift($tag['children'],$type_name_cell);
            $empty_cell = [
                'name' => 'table:table-cell',
                'attributes' => [
                    ['name' => 'table:style-name' , 'value' => 'ce33'],
                ],
                'children' => []
            ];
            array_unshift($tag['children'],$empty_cell);
            array_push($content,$tag);
        }

        $file_content .= $xml_generator->generateXMLTags($content);

        $content = [];
        $total = [
            'students'      => 0,
            'l8'            => 0,
            'a1'            => 0,
            'a2'            => 0,
            'b1'            => 0,
            'b2'            => 0,
            'b3'            => 0,
            'c'             => 0,
            'l8/l5b'        => 0,
            'percentage'    => 0
        ];
        $i = 1;

        foreach($second_sheet_rows as $row)
        {
            $tag = [
                'name' => 'table:table-row',
                'attributes' => [
                    ['name' => 'table:style-name', 'value' => 'ro2' ],
                ],
            ];

            $children = array_map(function($child) {
                return [
                    'name' => 'table:table-cell',
                    'attributes' => [
                        ['name' => 'table:style-name' , 'value' => 'ce33'],
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
            },$row);
            $tag['children'] = $children;
            
            $index = [
                'name' => 'table:table-cell',
                'attributes' => [
                    ['name' => 'table:style-name' , 'value' => 'ce33'],
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

            $total['students']      += $row['total_students'];
            $total['l8']            += $row['l8'];
            $total['a1']            += $row['A1'];
            $total['a2']            += $row['A2'];
            $total['b1']            += $row['B1'];
            $total['b2']            += $row['B2'];
            $total['b3']            += $row['B3'];
            $total['c']             += $row['C'];
            $total['l8/l5b']        += $row['l8/l5b'];
            // $total['percentage']    += $row['percent'];

            array_unshift($tag['children'],$index);
            array_push($content,$tag);
        }

        $file_content .= '</table:table>
        <table:table table:name="G120 tổng" table:style-name="ta1">
         <table:table-column table:style-name="co1" table:number-columns-repeated="8" table:default-cell-style-name="Default"/>
         <table:table-column table:style-name="co1" table:number-columns-repeated="6" table:default-cell-style-name="ce10"/>
         <table:table-row table:style-name="ro1">
          <table:table-cell table:style-name="ce2" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="14" table:number-rows-spanned="1">
           <text:p>G120 - BẢNG TỔNG HỢP TUẦN 4</text:p>
          </table:table-cell>
          <table:covered-table-cell table:number-columns-repeated="8" table:style-name="ce9"/>
          <table:covered-table-cell table:number-columns-repeated="5"/>
         </table:table-row>
         <table:table-row table:style-name="ro2">
          <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="14" table:number-rows-spanned="1">
           <text:p>Ngày khai giảng:</text:p>
          </table:table-cell>
          <table:covered-table-cell table:number-columns-repeated="7" table:style-name="ce10"/>
          <table:covered-table-cell table:number-columns-repeated="6"/>
         </table:table-row>
         <table:table-row table:style-name="ro2">
          <table:table-cell table:style-name="ce3" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="14" table:number-rows-spanned="1">
           <text:p>Ngày báo cáo:</text:p>
          </table:table-cell>
          <table:covered-table-cell table:number-columns-repeated="7" table:style-name="ce10"/>
          <table:covered-table-cell table:number-columns-repeated="6"/>
         </table:table-row>
         <table:table-row table:style-name="ro6">
          <table:table-cell table:number-columns-repeated="8"/>
          <table:table-cell table:style-name="Default" table:number-columns-repeated="6"/>
         </table:table-row>
         <table:table-row table:style-name="ro3">
          <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
           <text:p><text:s text:c="2"/>STT</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
           <text:p>Khu vực</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
           <text:p>QLHT Lớp</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
           <text:p>Ngành đăng ký</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
           <text:p>Tên lớp</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
           <text:p>Số lượng SV lớp</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
           <text:p>L8</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="6" table:number-rows-spanned="1">
           <text:p>Trạng thái G120</text:p>
          </table:table-cell>
          <table:covered-table-cell table:number-columns-repeated="5" table:style-name="ce5"/>
          <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
           <text:p>Tỷ lệ</text:p>
          </table:table-cell>
          <table:covered-table-cell table:style-name="ce5"/>
         </table:table-row>
         <table:table-row table:style-name="ro3">
          <table:covered-table-cell table:style-name="ce5"/>
          <table:covered-table-cell table:number-columns-repeated="6" table:style-name="ce34"/>
          <table:table-cell table:style-name="ce34" office:value-type="string" calcext:value-type="string">
           <text:p>A1</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce34" office:value-type="string" calcext:value-type="string">
           <text:p>A2</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce34" office:value-type="string" calcext:value-type="string">
           <text:p>B1</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce34" office:value-type="string" calcext:value-type="string">
           <text:p>B2</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce34" office:value-type="string" calcext:value-type="string">
           <text:p>B3</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce34" office:value-type="string" calcext:value-type="string">
           <text:p>C</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce34" office:value-type="string" calcext:value-type="string">
           <text:p>L8/L5B</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
           <text:p>Tỷ lệ</text:p>
          </table:table-cell>
         </table:table-row>
         <table:table-row table:style-name="ro3">
          <table:table-cell table:style-name="ce32" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="5" table:number-rows-spanned="1">
           <text:p>Tổng</text:p>
          </table:table-cell>
          <table:covered-table-cell table:number-columns-repeated="4" table:style-name="ce32"/>
          <table:table-cell table:style-name="ce32" office:value-type="float" office:value="'.$total['students'].'" calcext:value-type="float">
           <text:p>'.$total['students'].'</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce32" office:value-type="float" office:value="'.$total['l8'].'" calcext:value-type="float">
           <text:p>'.$total['l8'].'</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce32" office:value-type="float" office:value="'.$total['a1'].'" calcext:value-type="float">
           <text:p>'.$total['a1'].'</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce32" office:value-type="float" office:value="'.$total['a2'].'" calcext:value-type="float">
           <text:p>'.$total['a2'].'</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce32" office:value-type="float" office:value="'.$total['b1'].'" calcext:value-type="float">
           <text:p>'.$total['b1'].'</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce32" office:value-type="float" office:value="'.$total['b2'].'" calcext:value-type="float">
           <text:p>'.$total['b2'].'</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce32" office:value-type="float" office:value="'.$total['b3'].'" calcext:value-type="float">
           <text:p>'.$total['b3'].'</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce32" office:value-type="float" office:value="'.$total['c'].'" calcext:value-type="float">
           <text:p>'.$total['c'].'</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce32" office:value-type="float" office:value="'.$total['l8/l5b'].'" calcext:value-type="float">
           <text:p>'.$total['l8/l5b'].'</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce32" office:value-type="float" office:value="'.round($total['l8']/$total['students']*100,2).'" calcext:value-type="float">
           <text:p>'.round($total['l8']/$total['students']*100,2).'</text:p>
          </table:table-cell>
         </table:table-row>
        ';

        $file_content .= $xml_generator->generateXMLTags($content);
        $sheet_2_footer = [
            'name' => 'table:table-row',
            'attributes' => [
                ['name' => 'table:style-name' , 'value' => 'ro6'],
                ['name' => 'table:number-rows-repeated' , 'value' => '3'],
            ],
            'children' => [
                [
                    'name' => 'table:table-cell',
                    'attributes' => [
                        ['name' => 'table:number-columns-repeated' , 'value' => '14'],
                    ]
                ]
            ]
        ];

        $file_content .= $xml_generator->generateXMLTags([$sheet_2_footer]).'
            <table:table-row table:style-name="ro6">
            <table:table-cell table:style-name="ce41" table:number-columns-repeated="7"/>
            <table:table-cell table:style-name="ce41" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="7" table:number-rows-spanned="1">
            <text:p>......., ngày.............tháng....năm 20...</text:p>
            </table:table-cell>
            <table:covered-table-cell table:number-columns-repeated="6" table:style-name="ce44"/>
        </table:table-row>
        <table:table-row table:style-name="ro6">
            <table:table-cell table:style-name="ce41" table:number-columns-repeated="2"/>
            <table:table-cell table:style-name="ce41" office:value-type="string" calcext:value-type="string">
            <text:p>Giáo vụ</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce41"/>
            <table:table-cell table:style-name="ce41" office:value-type="string" calcext:value-type="string">
            <text:p>Kế toán</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce41" table:number-columns-repeated="2"/>
            <table:table-cell table:style-name="ce41" office:value-type="string" calcext:value-type="string">
            <text:p>TVTS</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce41" table:number-columns-repeated="3"/>
            <table:table-cell table:style-name="ce41" office:value-type="string" calcext:value-type="string">
            <text:p>QLHT</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce41" table:number-columns-repeated="2"/>
        </table:table-row>
        </table:table>
                <table:named-expressions/>
            </office:spreadsheet>
            </office:body>
            </office:document>';

        // $staff_name = $this->g120_repository->getStaff($request['staff'])->fullname;
            return $file_content;
    }
}