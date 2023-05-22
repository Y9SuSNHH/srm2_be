<?php

namespace App\Http\Domain\Reports\Services\P825;

use App\Http\Domain\Reports\Repositories\P825\P825RepositoryInterface;
use App\Http\Domain\Reports\Services\P825\P825MainService;
use App\Http\Enum\StudentStatus;
use App\Http\Domain\Reports\Services\XmlGenerator;
use App\Http\Enum\LockDay;
use App\Http\Enum\StudentTypeWeek;
use Carbon\Carbon;

class P825ExportService
{
    /** @var \App\Http\Domain\Reports\Repositories\P825\P825Repository */
    private $p825_repository;

    private $p825_main_service;
    
    public function __construct(P825RepositoryInterface $p825_repository,P825MainService $p825_main_service)
    {
        $this->p825_repository = $p825_repository;
        $this->p825_main_service = $p825_main_service;
    }

    public function generateFile($request) {
        $data = $this->p825_main_service->getAll($request,'export');
        $xml_generator = new XmlGenerator;
        $file_content = file_get_contents(storage_path(sprintf('app/template/%s','template_p825.fods')));

        $file_content .= '
        <table:table-row table:style-name="ro3">
            <table:table-cell table:style-name="ce4"/>
            <table:table-cell table:style-name="ce11" office:value-type="string" calcext:value-type="string">
                <text:p>Ngày báo cáo</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce12" office:value-type="string" calcext:value-type="string">
                <text:p>'.date('d/m/Y').'</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce4" table:number-columns-repeated="9"/>
        </table:table-row>
        <table:table-row table:style-name="ro2" table:number-rows-repeated="2">
            <table:table-cell table:style-name="ce4" table:number-columns-repeated="12"/>
        </table:table-row>
        <table:table-row table:style-name="ro4">
            <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
                <text:p>STT</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
                <text:p>QLHT lớp</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
                <text:p>Ngành đăng ký</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
                <text:p>Đối tượng</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
                <text:p>Tên lớp</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
                <text:p>Khu vực</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
                <text:p>Ngày ký G820</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
                <text:p>Đợt n-1</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
                <text:p>Đợt n</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
                <text:p>Tổng số sinh viên đợt n-1</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
                <text:p>Số lượng sinh viên đợt n</text:p>
            </table:table-cell>
            <table:table-cell table:style-name="ce5" office:value-type="string" calcext:value-type="string">
                <text:p>Tỷ lệ SL đầu đợt n/SL đầu đợt n-1</text:p>
            </table:table-cell>
        </table:table-row>';

        $content = [];
        $sum = [
            'n-1' => 0,
            'n' => 0,
            'percentage' => 0,
        ];
        $i = 1;
        foreach($data as $dat)
        {
            $sum['n'] += $dat['n_semester_count'];
            $sum['n-1'] += $dat['n_1_semester_count'];
            $tag = [
                'name' => 'table:table-row',
                'attributes' => [
                    ['name' => 'table:style-name', 'value' => 'ro2' ],
                ],
            ];

            $children = array_values(array_map(function($child) {
                return [
                    'name' => 'table:table-cell',
                    'attributes' => [
                        ['name' => 'table:style-name' , 'value' => 'ce6'],
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
            },$dat));

            $tag['children'] = $children;
            $index = [
                'name' => 'table:table-cell',
                'attributes' => [
                    ['name' => 'table:style-name' , 'value' => 'ce6'],
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

        $sum_tag = [
            'name' => 'table:table-row',
            'attributes' => [
                ['name' => 'table:style-name', 'value' => 'ro5' ],
            ],
        ];

        $sum['percentage'] = $sum['n-1'] != 0 ? round($sum['n']/$sum['n-1']*100,2).'%' : '0%';

        $children = array_values(array_map(function ($child) {
            return [
                'name' => 'table:table-cell',
                'attributes' => [
                    ['name' => 'table:style-name' , 'value' => 'ce6'],
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
        },$sum));
        $sum_tag['children'] = $children;

        $sum_placeholder = 
        [
            'name' => 'table:table-cell',
            'attributes' => [
                ['name' => 'table:style-name' , 'value' => 'ce8'],
                ['name' => 'office:value-type' , 'value' => 'string'],
                ['name' => 'table:number-columns-spanned','value' => 9],
                ['name' => 'table:number-rows-spanned','value' => 1],
            ],
            'children' => [
                [
                    'name' => 'text:p',
                    'value' => 'Tổng',
                ]
            ]
        ];
        $sum_tag_cover = 
        [
            'name' => 'table:covered-table-cell',
            'attributes' => [
                ['name' => 'table:number-columns-repeated','value' => 8],
                ['name' => 'table:style-name','value' => 'ce8'],
            ]
        ];
        array_unshift($sum_tag['children'],$sum_tag_cover);
        array_unshift($sum_tag['children'],$sum_placeholder);
        array_push($content,$sum_tag);
        $file_content .= $xml_generator->generateXMLTags($content);

        $file_content .= '
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