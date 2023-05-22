<?php

namespace App\Http\Domain\TrainingProgramme\Services\Curriculum;

use Carbon\Carbon;

class CreateCurriculum
{
    public function processDataToCreate($request,$process_array) {
        $major_objects = $process_array['major_objects'];
        $list_learning_modules = $process_array['list_learning_modules'];
        $curriculum_id = $process_array['new_curriculum']->id;
        $enrollment_object = call_user_func_array('array_merge',array_map(function($object) {
            return $object;
        },$major_objects));
        $insert_records = [];

        foreach($request['list_learning_modules'] as $learning_module)
        {
            $subject = $learning_module['subject_id'];
            $insert_objects = $learning_module['objects'];
            $object_names = array_keys($insert_objects);

            if(!empty($insert_records))
            {
                $has_the_same_subject = array_filter(array_map(function($item) use ($subject){
                    if($item['subject_id'] == $subject)
                        return $item['enrollment_object_id'];
                    return [];
                },$insert_records));

                // Check if the update request has objects that have already existed
                $unique_violated_records = array_filter(array_map(function($key,$object) use ($enrollment_object,$has_the_same_subject,$process_array){
                    if($object != 0 && array_key_exists($key,$enrollment_object) && in_array($enrollment_object[$key],$has_the_same_subject))
                        return $key;
                    return [];
                },$object_names,$insert_objects));

                // And if they truthly have, stop immediately
                if(!empty($unique_violated_records))
                {
                    return [
                        'learning_module'         => $learning_module['code'],
                        'unique_violated_objects' => implode(',',$unique_violated_records)
                    ];
                }
            }

            // These are the one which has update request and not exist in any items of this curriculum 
            $insert_objects = array_filter(array_map(function($key,$object) use ($enrollment_object,$learning_module,$subject,$curriculum_id){
                if($object != 0 && array_key_exists($key,$enrollment_object))
                {
                    return [
                        "training_program_id"   => $curriculum_id,
                        "learning_module_id"    => $learning_module['learning_module_id'],
                        "enrollment_object_id"  => $enrollment_object[$key],
                        "subject_id"            => $subject,
                    ];
                }

                return [];
            },$object_names,$insert_objects));

            $insert_records = array_merge($insert_records,$insert_objects);
        }

        return $insert_records;
    }

}