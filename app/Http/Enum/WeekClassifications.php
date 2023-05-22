<?php

namespace App\Http\Enum;

use Carbon\Carbon;

class WeekClassifications
{
    private const W1_CLASSIFICATION = [
        //Has paid all fees
        1 => [
            // Has profile_status = 'QDNH_HS_CUNG'
            1 => [
                // Move to another time to study
                1 => [
                    // Joining first day of school
                    1 => [
                        // Week 1 attendance
                        1 => 'B3',
                        0 => 'B3'
                    ],
                    0 => [
                        1 => 'B3',
                        0 => 'B3'
                    ]
                ],
                0 => [
                    1 => [
                        1 => 'A1',
                        0 => 'B1'
                    ],
                    0 => [
                        1 => 'A2',
                        0 => 'B1'
                    ]
                ]
            ],
            0 => [
                1 => [
                    1 => [
                        1 => 'A1',
                        0 => 'B1'
                    ],
                    0 => [
                        1 => 'A2',
                        0 => 'B1'
                    ]
                ],
                0 => [
                    1 => [
                        1 => 'A1',
                        0 => 'B1'
                    ],
                    0 => [
                        1 => 'A2',
                        0 => 'B1'
                    ]
                ]
            ],
        ],
        0 => [
            1 => [
                1 => [
                    1 => [
                        1 => 'C',
                        0 => 'C'
                    ],
                    0 => [
                        1 => 'C',
                        0 => 'C'
                    ]
                ],
                0 => [
                    1 => [
                        1 => 'C',
                        0 => 'C'
                    ],
                    0 => [
                        1 => 'C',
                        0 => 'C'
                    ]
                ]
            ],
            0 => [
                1 => [
                    1 => [
                        1 => 'C',
                        0 => 'C'
                    ],
                    0 => [
                        1 => 'C',
                        0 => 'C'
                    ]
                ],
                0 => [
                    1 => [
                        1 => 'C',
                        0 => 'C'
                    ],
                    0 => [
                        1 => 'C',
                        0 => 'C'
                    ]
                ]
            ]
        ]
    ];

    private const W3_CLASSIFICATION = [
        // Has profile_status = 'QDNH_HS_CUNG'
        1 => [
            // Move to another time to study
            1 => [
                // Joining first day of school
                1 => [
                    // Week 1 attendance
                    1 => [
                        // Week 2 attendance
                        1 => 'B3',
                        0 => 'B3'
                    ],
                    0 => [
                        1 => 'B3',
                        0 => 'B3'
                    ]
                ],
                0 => [
                    1 => [
                        1 => 'B3',
                        0 => 'B3'
                    ],
                    0 => [
                        1 => 'B3',
                        0 => 'B3'
                    ]
                ],
            ],
            0 => [
                1 => [
                    1 => [
                        1 => 'A1',
                        0 => 'B2_HT'
                    ],
                    0 => [
                        1 => 'A2',
                        0 => 'B2_HT'
                    ]
                ],
                0 => [
                    1 => [
                        1 => 'A2',
                        0 => 'B2_HT'
                    ],
                    0 => [
                        1 => 'A2',
                        0 => 'B2_HT'
                    ]
                ],
            ]
        ],
        0 => [
            1 => [
                1 => [
                    1 => [
                        1 => 'A1',
                        0 => 'B2_HT'
                    ],
                    0 => [
                        1 => 'A2',
                        0 => 'B2_HT'
                    ]
                ],
                0 => [
                    1 => [
                        1 => 'A2',
                        0 => 'B2_HT'
                    ],
                    0 => [
                        1 => 'A2',
                        0 => 'B2_HT'
                    ]
                ],
            ],
            0 => [
                1 => [
                    1 => [
                        1 => 'A1',
                        0 => 'B2_HT'
                    ],
                    0 => [
                        1 => 'A2',
                        0 => 'B2_HT'
                    ]
                ],
                0 => [
                    1 => [
                        1 => 'A2',
                        0 => 'B2_HT'
                    ],
                    0 => [
                        1 => 'A2',
                        0 => 'B2_HT'
                    ]
                ],
            ]
        ]
    ];

    public static function makeClassification($week,$request)
    {
        switch($week) {
            case 1:
                // $x = self::W1_CLASSIFICATION[$request['difference']][$request['has_certain_profile_status']][$request['study_later']][$request['is_join_first_day_of_school']][$request['is_join_first_week']];
                return self::W1_CLASSIFICATION[$request['difference']][$request['has_certain_profile_status']][$request['study_later']][$request['is_join_first_day_of_school']][$request['is_join_first_week']];
            case 3:
                return self::W3_CLASSIFICATION[$request['has_certain_profile_status']][$request['study_later']][$request['is_join_first_day_of_school']][$request['is_join_first_week']][$request['is_join_fourth_week']];
            default:
                return 'Invalid week';
        }
    }
}