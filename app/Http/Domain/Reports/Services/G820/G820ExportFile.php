<?php

namespace App\Http\Domain\Reports\Services\G820;

use App\Http\Domain\Reports\Repositories\G820\G820RepositoryInterface;
use App\Http\Domain\Reports\Services\G820\ExecuteData;
use App\Http\Enum\StudentStatus;
use App\Http\Domain\Reports\Services\XmlGenerator;
use App\Http\Enum\LockDay;
use Carbon\Carbon;

class G820ExportFile
{
    /** @var \App\Http\Domain\Reports\Repositories\G820\G820Repository */
    private $g820_repository;

    private $data_service;
    
    public function __construct(G820RepositoryInterface $g820_repository,ExecuteData $data_service)
    {
        $this->g820_repository = $g820_repository;
        $this->data_service = $data_service;
    }

    public function generateFile($request)
    {
        [ $g820a_data,$chosen_semester,$max_semester,$students ] = $this->data_service->getAll($request,'export');
        [ $g820b_data,$sum ] = $this->data_service->dataForG820B($students,$chosen_semester);
        $xml_generator = new XmlGenerator;
        $file_content = file_get_contents(storage_path(sprintf('app/template/%s','template_g820.fods')));

        $file_content .= '
        <office:body>
        <office:spreadsheet>
         <table:calculation-settings table:automatic-find-labels="false" table:use-regular-expressions="false" table:use-wildcards="true"/>
         <table:table table:name="G820_A" table:style-name="ta1">
          <table:table-column table:style-name="co1" table:default-cell-style-name="ce1"/>
          <table:table-column table:style-name="co1" table:default-cell-style-name="ce35"/>
          <table:table-column table:style-name="co4" table:default-cell-style-name="ce35"/>
          <table:table-column table:style-name="co5" table:default-cell-style-name="ce35"/>
          <table:table-column table:style-name="co1" table:number-columns-repeated="3" table:default-cell-style-name="ce35"/>
          <table:table-column table:style-name="co6" table:default-cell-style-name="ce35"/>
          <table:table-column table:style-name="co1" table:default-cell-style-name="ce35"/>
          <table:table-column table:style-name="co7" table:default-cell-style-name="ce14"/>
          <table:table-column table:style-name="co10" table:default-cell-style-name="ce14"/>
          <table:table-column table:style-name="co11" table:default-cell-style-name="ce14"/>
          <table:table-column table:style-name="co1" table:number-columns-repeated="17" table:default-cell-style-name="ce14"/>
          <table:table-column table:style-name="co1" table:number-columns-repeated="26" table:default-cell-style-name="ce40"/>
          <table:table-row table:style-name="ro1">
           <table:table-cell office:value-type="string" calcext:value-type="string" table:number-columns-spanned="12" table:number-rows-spanned="1">
            <text:p>G820A_DANH SÁCH CHI TIẾT SINH VIÊN PHẢI THU ĐẦU KỲ</text:p>
           </table:table-cell>
           <table:covered-table-cell table:number-columns-repeated="8"/>
           <table:covered-table-cell table:number-columns-repeated="3" table:style-name="ce35"/>
           <table:table-cell table:style-name="ce35" table:number-columns-repeated="11"/>
           <table:table-cell table:style-name="ce40" table:number-columns-repeated="6"/>
           <table:table-cell table:number-columns-repeated="26"/>
          </table:table-row>
          <table:table-row table:style-name="ro2">
           <table:table-cell table:style-name="ce30" table:number-columns-repeated="9"/>
           <table:table-cell table:number-columns-repeated="20"/>
           <table:table-cell table:style-name="ce14" table:number-columns-repeated="2"/>
           <table:table-cell table:number-columns-repeated="24"/>
          </table:table-row>
          <table:table-row table:style-name="ro2">
           <table:table-cell table:style-name="ce5"/>
           <table:table-cell table:style-name="ce12" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
            <text:p>Ngày báo cáo:</text:p>
           </table:table-cell>
           <table:covered-table-cell table:style-name="Default"/>
           <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
            <text:p>'.date('d/m/Y').'</text:p>
           </table:table-cell>
           '
           .($max_semester != '' ? '
           <table:table-cell table:style-name="Default" table:number-columns-repeated="2"/>
           <table:table-cell table:style-name="ce12" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="2" table:number-rows-spanned="1">
            <text:p>Kỳ thu học phí:</text:p>
           </table:table-cell>
           <table:covered-table-cell table:style-name="Default"/>
           <table:table-cell table:style-name="ce5" office:value-type="float" office:value="'.$max_semester.'" calcext:value-type="float">
            <text:p>'.$max_semester.'</text:p>
           </table:table-cell>
           <table:table-cell table:number-columns-repeated="46"/>' : '').
           
           '
          </table:table-row>
          <table:table-row table:style-name="ro2">
           <table:table-cell table:style-name="ce6" office:value-type="string" calcext:value-type="string">
            <text:p><text:s/></text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce6" table:number-columns-repeated="8"/>
           <table:table-cell table:number-columns-repeated="20"/>
           <table:table-cell table:style-name="ce14" table:number-columns-repeated="2"/>
           <table:table-cell table:number-columns-repeated="24"/>
          </table:table-row>
          <table:table-row table:style-name="ro2">
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>STT</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Mã hồ sơ</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Mã sinh viên</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Họ đệm</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Tên</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Phái</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Ngày sinh</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Điện thoại</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Nơi sinh</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Account học tập</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Email học tập</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Địa chỉ liên hệ</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Ngành học</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Lớp</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Đối tượng</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Khu vực</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Số QĐ</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>Ngày QĐ</text:p>
           </table:table-cell>
           <table:table-cell table:style-name="ce33" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="1" table:number-rows-spanned="2">
            <text:p>QLHT</text:p>
           </table:table-cell>
        ';
        
        $flex_header_first_line = [];
        for($i = 1;$i<= $max_semester;$i++)
        {
            $tag = [
                'name' => 'table:table-cell',
                'attributes' => [
                    ['name' => 'table:style-name', 'value' => $i == 1 ? 'ce38' : 'ce39' ],
                    ['name' => 'table:number-columns-spanned', 'value' => 4 ],
                    ['name' => 'table:number-rows-spanned', 'value' => 1 ],
                ],
                'children' => [
                    [
                        'name' => 'text:p',
                        'value' => 'Đợt '.$i,
                    ]
                ]
            ];
            array_push($flex_header_first_line,$tag);
            $tag_span = [
                'name' => 'table:covered-table-cell',
                'attributes' => [
                    ['name' => 'table:style-name', 'value' => $i == 1 ? 'ce38' : 'ce39' ],
                    ['name' => 'table:number-columns-repeated', 'value' => 3 ],
                ],
            ];
            array_push($flex_header_first_line,$tag_span);
        }

        $file_content .= $xml_generator->generateXMLTags($flex_header_first_line).'
        <table:table-cell table:number-columns-repeated="20"/>
        </table:table-row>
        <table:table-row table:style-name="ro7">
        <table:covered-table-cell table:number-columns-repeated="19" table:style-name="ce33"/>
        ';

        $flex_header_second_line = [];
        $semester_children = [
            'Phải thu',
            'Thực thu',
            'Chênh lệch (Phải thu - Thực thu)',
            'Trạng thái sinh viên đầu kỳ'
        ];
        for($i = 1;$i<= $max_semester;$i++)
        {
            $tags = array_map(function($child) {
                return [
                    'name' => 'table:table-cell',
                    'attributes' => [
                        ['name' => 'table:style-name', 'value' => 'ce39' ],
                    ],
                    'children' => [
                        [
                            'name' => 'text:p',
                            'value' => $child,
                        ]
                    ]
                ];
            },$semester_children);
            array_push($flex_header_second_line,$tags);
        }
        $flex_header_second_line = call_user_func_array('array_merge',array_map(function($tag) {
            return $tag;
        },$flex_header_second_line));

        $file_content .= $xml_generator->generateXMLTags($flex_header_second_line).'
        <table:table-cell table:number-columns-repeated="20"/>
        </table:table-row>';

        $content = [];
        $i = 1;

        foreach($g820a_data as $dat)
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
                        ['name' => 'table:style-name' , 'value' => 'ce10'],
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
            },$dat);
            
            $tag['children'] = $children;
            $index = [
                'name' => 'table:table-cell',
                'attributes' => [
                    ['name' => 'table:style-name' , 'value' => 'ce10'],
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
            $end_row_tag = [
                'name' => 'table:table-cell',
                'attributes' => [
                    ['name' => 'table:style-name' , 'value' => 'ce14'],
                    ['name' => 'table:number-columns-repeated' , 'value' => '20'],
                ],
            ];
            array_push($tag['children'],$end_row_tag);
            array_push($content,$tag);
            $i++;
        }

        $file_content .= $xml_generator->generateXmlTags($content).'
        </table:table>
        <table:table table:name="G820_B" table:style-name="ta1">
         <table:table-column table:style-name="co1" table:default-cell-style-name="Default"/>
         <table:table-column table:style-name="co8" table:default-cell-style-name="Default"/>
         <table:table-column table:style-name="co9" table:default-cell-style-name="Default"/>
         <table:table-column table:style-name="co1" table:number-columns-repeated="15" table:default-cell-style-name="Default"/>
         <table:table-row table:style-name="ro1">
          <table:table-cell table:style-name="ce41" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="11" table:number-rows-spanned="1">
           <text:p>G820B_ BẢNG TỔNG HỢP SỐ LƯỢNG ĐẦU ĐỢT HỌC</text:p>
          </table:table-cell>
          <table:covered-table-cell table:number-columns-repeated="10" table:style-name="ce45"/>
          <table:table-cell table:style-name="ce40" table:number-columns-repeated="6"/>
          <table:table-cell table:style-name="ce49"/>
         </table:table-row>
         <table:table-row table:style-name="ro2">
          <table:table-cell table:style-name="ce30"/>
          <table:table-cell table:style-name="ce40" office:value-type="string" calcext:value-type="string">
           <text:p>Ngày báo cáo</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce40" office:value-type="string" calcext:value-type="string">
           <text:p>'.date('d/m/Y').'</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce30" table:number-columns-repeated="6"/>
          <table:table-cell table:style-name="ce47" table:number-columns-spanned="4" table:number-rows-spanned="1"/>
          <table:covered-table-cell table:number-columns-repeated="3" table:style-name="ce40"/>
          <table:table-cell table:style-name="ce40" table:number-columns-repeated="5"/>
         </table:table-row>
         <table:table-row table:style-name="ro2">
          <table:table-cell table:style-name="ce40" table:number-columns-repeated="18"/>
         </table:table-row>
         <table:table-row table:style-name="ro5">
          <table:table-cell table:style-name="ce42" office:value-type="string" calcext:value-type="string">
           <text:p>STT</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce42" office:value-type="string" calcext:value-type="string">
           <text:p>QLHT lớp</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce42" office:value-type="string" calcext:value-type="string">
           <text:p>Ngành đăng ký</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce42" office:value-type="string" calcext:value-type="string">
           <text:p>Đối tượng</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce42" office:value-type="string" calcext:value-type="string">
           <text:p>Tên lớp</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce42" office:value-type="string" calcext:value-type="string">
           <text:p>Khu vực</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce42" office:value-type="string" calcext:value-type="string">
           <text:p>Đợt học</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce42" office:value-type="string" calcext:value-type="string">
           <text:p>Ngày ký G820</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce42" office:value-type="string" calcext:value-type="string">
           <text:p>TỔNG</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce48" office:value-type="string" calcext:value-type="string">
           <text:p>00_XOA_QUYET_DINH_SV </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce48" office:value-type="string" calcext:value-type="string">
           <text:p>03_NGHI_HOC </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce42" office:value-type="string" calcext:value-type="string">
           <text:p>04_BAO_LUU</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce42" office:value-type="string" calcext:value-type="string">
           <text:p>05_TAM_NGUNG_HOC_DO_CHUA_HS_HP</text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce48" office:value-type="string" calcext:value-type="string">
           <text:p>06_DANG_HOC_CHUA_HS </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce48" office:value-type="string" calcext:value-type="string">
           <text:p>07_DANG_HOC_CHO_QĐNH </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce48" office:value-type="string" calcext:value-type="string">
           <text:p>08_DANG_HOC_DA_CO_QĐNH </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce48" office:value-type="string" calcext:value-type="string">
           <text:p>09_DA_TOT_NGHIEP </text:p>
          </table:table-cell>
          <table:table-cell table:style-name="ce42" office:value-type="string" calcext:value-type="string">
           <text:p>SV đang học đóng HP kỳ trước</text:p>
          </table:table-cell>
         </table:table-row>
        ';
        
        $content = [];
        $i = 1;
        foreach($g820b_data as $dat)
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
                        ['name' => 'table:style-name' , 'value' => 'ce43'],
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
            },$dat);
            
            $tag['children'] = $children;
            $index = [
                'name' => 'table:table-cell',
                'attributes' => [
                    ['name' => 'table:style-name' , 'value' => 'ce43'],
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
        $file_content .= $xml_generator->generateXmlTags($content).'
            <table:table-row table:style-name="ro6">
            <table:table-cell table:style-name="ce44" office:value-type="string" calcext:value-type="string" table:number-columns-spanned="8" table:number-rows-spanned="1">
            <text:p>Tổng</text:p>
            </table:table-cell>
            <table:covered-table-cell table:number-columns-repeated="7" table:style-name="ce44"/>
            <table:table-cell table:style-name="ce46" office:value-type="float" office:value="'.$sum['total_students'].'" calcext:value-type="float">
            <text:p>'.$sum['total_students'].'</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce46" office:value-type="float" office:value="'.$sum['all_status_count'][StudentStatus::statusForReports(StudentStatus::XOA_QUYET_DINH_SINH_VIEN)].'" calcext:value-type="float">
            <text:p>'.$sum['all_status_count'][StudentStatus::statusForReports(StudentStatus::XOA_QUYET_DINH_SINH_VIEN)].'</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce46" office:value-type="float" office:value="'.$sum['all_status_count'][StudentStatus::statusForReports(StudentStatus::NGHI_HOC)].'" calcext:value-type="float">
            <text:p>'.$sum['all_status_count'][StudentStatus::statusForReports(StudentStatus::NGHI_HOC)].'</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce46" office:value-type="float" office:value="'.$sum['all_status_count'][StudentStatus::statusForReports(StudentStatus::BAO_LUU)].'" calcext:value-type="float">
            <text:p>'.$sum['all_status_count'][StudentStatus::statusForReports(StudentStatus::BAO_LUU)].'</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce46" office:value-type="float" office:value="'.$sum['all_status_count'][StudentStatus::statusForReports(StudentStatus::TAM_NGUNG_HOC_DO_CHUA_HS_HP)].'" calcext:value-type="float">
            <text:p>'.$sum['all_status_count'][StudentStatus::statusForReports(StudentStatus::TAM_NGUNG_HOC_DO_CHUA_HS_HP)].'</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce46" office:value-type="float" office:value="'.$sum['all_status_count'][StudentStatus::statusForReports(StudentStatus::DANG_HOC_CHUA_HS)].'" calcext:value-type="float">
            <text:p>'.$sum['all_status_count'][StudentStatus::statusForReports(StudentStatus::DANG_HOC_CHUA_HS)].'</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce46" office:value-type="float" office:value="'.$sum['all_status_count'][StudentStatus::statusForReports(StudentStatus::DANG_HOC_CHO_QDNH)].'" calcext:value-type="float">
            <text:p>'.$sum['all_status_count'][StudentStatus::statusForReports(StudentStatus::DANG_HOC_CHO_QDNH)].'</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce46" office:value-type="float" office:value="'.$sum['all_status_count'][StudentStatus::statusForReports(StudentStatus::DANG_HOC_DA_CO_QDNH)].'" calcext:value-type="float">
            <text:p>'.$sum['all_status_count'][StudentStatus::statusForReports(StudentStatus::DANG_HOC_DA_CO_QDNH)].'</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce46" office:value-type="float" office:value="'.$sum['all_status_count'][StudentStatus::statusForReports(StudentStatus::DA_TOT_NGHIEP)].'" calcext:value-type="float">
            <text:p>'.$sum['all_status_count'][StudentStatus::statusForReports(StudentStatus::DA_TOT_NGHIEP)].'</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce46" office:value-type="float" office:value="'.$sum['unpaid_students'].'" calcext:value-type="float">
            <text:p>'.$sum['unpaid_students'].'</text:p>
            </table:table-cell>
        </table:table-row>
        </table:table>
        <table:named-expressions/>
       </office:spreadsheet>
      </office:body>
     </office:document>
        ';

        return $file_content;
    }
}