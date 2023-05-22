<?php

namespace App\Http\Domain\TrainingProgramme\Services\Curriculum;

use Carbon\Carbon;

class UpdateCurriculum
{
    public function processUpdateData($request,$process_array)
    {   
        // Data to use for insert new objects or delete existing one
        $enrollment_object = $process_array['enrollment_object'];
        $curriculum = $process_array['curriculum'];
        $curriculum_items = $process_array['curriculum_items'];
        $subject_id = $process_array['subject_id'];
        $enrollment_object = $process_array['enrollment_object'];
        $existed_objects = $process_array['existed_objects'];
        $learning_module_id = $process_array['learning_module_id'];

        // Transform objects arrays with keys area their names and values are their IDs
        $update_objects = $request->objects;
        $object_names = array_keys($update_objects);
        $enrollment_object = call_user_func_array('array_merge',array_map(function($object) {
            return $object;
        },$enrollment_object));

        // Check if the update request has objects that have already existed
        $unique_violated_records = array_filter(array_map(function($key,$object) use ($enrollment_object,$existed_objects,$process_array){
            if($object != 0 && array_key_exists($key,$enrollment_object) && in_array($enrollment_object[$key],$process_array['duplicate_check']))
                return $key;

            return [];
        },$object_names,$update_objects));

        // And if they truthly have, stop immediately
        if(!empty($unique_violated_records))
        {
            return ['unique_violated_objects' => implode(',',$unique_violated_records)];
        }
        // These are the one which has update request and not exist in any items of this curriculum 
        $insert_objects = array_filter(array_map(function($key,$object) use ($enrollment_object,$existed_objects){
            if($object != 0 && array_key_exists($key,$enrollment_object) && !in_array($enrollment_object[$key],$existed_objects))
                return $enrollment_object[$key];

            return [];
        },$object_names,$update_objects));
        
        // And these objects have already had records for this learning module. Unfortunately, they'll be gone soon by the delete request
        $delete_objects = array_filter(array_map(function($key,$object) use ($enrollment_object,$existed_objects,$curriculum_items){
            if($object === 0 && array_key_exists($key,$enrollment_object) && in_array($enrollment_object[$key],$existed_objects))
            {
                return $enrollment_object[$key];
            }
                
            return [];
        },$object_names,$update_objects));

        // Format the incoming insert records to return
        if($insert_objects && $insert_objects != '')
        {
            $insert_data = array_filter(array_map(function ($object) use ($curriculum,$subject_id,$learning_module_id,$existed_objects) {
                if(!in_array($object,$existed_objects))
                {
                    return [
                        'training_program_id' => $curriculum->id,
                        'learning_module_id' => $learning_module_id,
                        'enrollment_object_id' => $object,
                        'subject_id' => $subject_id,
                        'created_at' => Carbon::now(),
                    ];
                }
                return [];
            },$insert_objects));
        }

        return [
            'insert' => isset($insert_data) ? $insert_data : [],
            'delete' => $delete_objects ? $delete_objects : []
        ];
    }
}