<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Eloquent\Curriculum;
use App\Eloquent\CurriculumItems;

class CurriculumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $list_curriculum = [
            [
                'school_id' => 2,
                'major_id' => 9,
                'began_date' => '2022-12-20'
            ],
            [
                'school_id' => 2,
                'major_id' => 9,
                'began_date' => '2023-02-10'
            ],
            [
                'school_id' => 2,
                'major_id' => 8,
                'began_date' => '2022-12-20'
            ]
        ];

        $list_curriculum_items = [
            [
                'training_program_id' => 4,
                'learning_module_id' => 1,
                'enrollment_object_id' => 13,
                'subject_id' => 1,
            ],
            [
                'training_program_id' => 4,
                'learning_module_id' => 1,
                'enrollment_object_id' => 14,
                'subject_id' => 1,
            ],
            [
                'training_program_id' => 4,
                'learning_module_id' => 1,
                'enrollment_object_id' => 15,
                'subject_id' => 1,
            ],
        ];

        Curriculum::upsert($list_curriculum,['school_id','major_id','began_date'],['updated_at' => date('Y-m-d H:i:s')]);
        CurriculumItems::upsert($list_curriculum_items,['training_program_id','enrollment_object_id','subject_id'],['updated_at' => date('Y-m-d H:i:s')]);
    }
}
