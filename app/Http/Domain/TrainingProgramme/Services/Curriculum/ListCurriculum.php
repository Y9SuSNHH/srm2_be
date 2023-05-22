<?php

namespace App\Http\Domain\TrainingProgramme\Services\Curriculum;

use App\Helpers\Request;
use Carbon\Carbon;
use App\Eloquent\Curriculum;
use App\Eloquent\Major;
use App\Eloquent\MajorObjectMap;
use App\Eloquent\LearningModule;

class ListCurriculum
{
    public function handle(array $curriculum_list,$repository) {
        // Find all curriculums's id,major_ids
        $curriculum_ids = array_map(function($curriculum){
            return $curriculum['id'];
        },$curriculum_list);

        $curriculum_major_ids = array_unique(array_map(function($curriculum){
            return $curriculum['get_major']['id'];
        },$curriculum_list));

        // Get list of items that belongs to these curriculums
        $list_items = $repository->getListItems($curriculum_ids);

        // Get these curriculums's learning modules's ids
        $curriculum_learning_module_ids = array_unique(array_map(function($curriculum){
            return $curriculum['learning_module_id'];
        },$list_items));

        // And use them to get objects and learning modules
        $list_object_by_major = $repository->getMajorObjects($curriculum_major_ids);
        $list_learning_module = $repository->getLearningModule($curriculum_learning_module_ids);
        $data = [];

        foreach($curriculum_list as $curriculum)
        {
            // Setup curriculum's position in return data array
            if(!isset($data[$curriculum['id']]))
            {
                $data[$curriculum['id']] = [];
            }

            // This curriculum's objects are...
            $list_objects = call_user_func_array("array_merge",array_filter(array_map(function ($object) use($curriculum) {
                if($object['id'] == $curriculum['major_id'])
                {
                    return [
                        'major'     => $object['major'],
                        'objects'   => $object['objects']
                    ];
                }
                return [];
            },$list_object_by_major)));

            // Add them to our return data
            $data[$curriculum['id']]['curriculum_id'] = $curriculum['id'];
            // $data[$curriculum['id']]['subject_id'] = $curriculum['get_learning_modules']['subject']['id'];
            $data[$curriculum['id']]['major'] = $list_objects['major'];
            $data[$curriculum['id']]['objects'] = $list_objects['objects'];

            // And see which learning modules belongs to this curriculum
            $list_learning_module_ids = array_unique(array_filter(array_map(function($item) use ($curriculum) {
                
                if($item['training_program_id'] == $curriculum['id'])
                {
                    return $item['learning_module_id'];
                } else {
                    return [];
                }
            },$list_items)));

            $curriculum_learning_module = array_filter(array_map(function ($learning_module) use ($list_learning_module_ids) {
                if(in_array($learning_module['id'],$list_learning_module_ids))
                {
                    // $learning_module[]
                    return $learning_module;
                }
                return [];
            },$list_learning_module));

            $data[$curriculum['id']]['total'] = count($curriculum_learning_module);
            // Finally, process all data that we've got so far
            $data_after_process = $this->processData($curriculum['id'],$curriculum_learning_module,$list_objects['objects'],$list_items);

            // Add the processed data to the return data
            $data[$curriculum['id']]['learning_module'] = $data_after_process['list_learning_module'];
            $data[$curriculum['id']]['sum'] = $data_after_process['sum'];
        }
        
        return $data;
    }

    private function processData($curriculum_id,array $list_learning_module,array $list_object,array $list_items)
    {
        $modules_after_process = [];

        // This is the sum of all credits that objects need to study
        $sum = call_user_func_array('array_merge',array_map(function($object) {
            return [$object => 0];
        },$list_object));

        // A copy of the sum to add in every single learning module to see if a specific object has to study this module
        $item_objects_credits = $sum;

        foreach($list_learning_module as $learning_module)
        {
            // Add the objects in first
            $learning_module['objects'] = $item_objects_credits;
            foreach($list_items as $item)
            {
                // If this item belongs to this learning module...
                if($item['training_program_id'] == $curriculum_id && 
                   $item['learning_module_id'] == $learning_module['id'] && 
                   in_array($item['get_objects']['shortcode'],$list_object))
                {
                    // Add the credits to the object stay in this learning module and in the sum
                    $learning_module['objects'][$item['get_objects']['shortcode']] = $learning_module['credits'];
                    $sum[$item['get_objects']['shortcode']] += $learning_module['credits'];
                }
            }
            
            // Push it in return data
            array_push($modules_after_process,$learning_module);
        }

        return [
            'sum' => $sum,
            'list_learning_module' => $modules_after_process
        ];
    }
}