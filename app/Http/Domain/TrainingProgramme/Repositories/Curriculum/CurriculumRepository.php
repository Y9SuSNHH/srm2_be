<?php

namespace App\Http\Domain\TrainingProgramme\Repositories\Curriculum;

use App\Eloquent\Curriculum as EloquentCurriculum;
use App\Http\Domain\TrainingProgramme\Requests\Curriculum\SearchRequest;
use App\Http\Domain\TrainingProgramme\Requests\Curriculum\EditCurriculumRequest;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Domain\TrainingProgramme\Models\Curriculum\Curriculum as CurriculumModel;
use App\Eloquent\CurriculumItems;
use App\Eloquent\Major;
use App\Eloquent\LearningModule;
use App\Http\Domain\TrainingProgramme\Services\Curriculum\CreateCurriculum;
use App\Http\Domain\TrainingProgramme\Services\Curriculum\UpdateCurriculum;
use Illuminate\Support\Facades\DB;

class CurriculumRepository implements CurriculumRepositoryInterface
{
    /** @var Builder|\Illuminate\Database\Eloquent\Model|EloquentCurriculum */
    private $eloquent_model;

    private $create_curriculum_service;

    private $update_curriculum_service;
    /**
     * OperatorRepository constructor.
     */
    public function __construct()
    {
        $this->eloquent_model = EloquentCurriculum::query()->getModel();
        $this->create_curriculum_service = new CreateCurriculum;
        $this->update_curriculum_service = new UpdateCurriculum;
    }

    /**
     */
    public function getAll(SearchRequest $request)    
    {
        $search_request = $request->all();
        $query = $this->eloquent_model->query()->with(['getMajor' =>function($q) {
            $q->with('getObjects');
        },'getItems' => function($q) {
            $q->with(['getLearningModule' => function($q) {
                $q->with('subject');
            }]);
        }]);
        
        if(isset($search_request['major']) && $search_request['major'] != '' && $search_request['major'] != 'all')
        {
            $query->where('major_id',$search_request['major']);
        }
        
        if(isset($search_request['began_date']) && $search_request['began_date'] != '' && $search_request['began_date'] != 'all')
        {
            $query->where('began_date',$search_request['began_date']);
        }
        
        return $query->orderBy('began_date','DESC')->get()->toArray();
    }

    /**
     * @param int $id
     * @return array
     */
    public function getById(int $id): array
    {
        $major = $this->eloquent_model->query()->findOrFail($id)->toArray();
        return (array)new CurriculumModel($major);
    }

    public function getListItems($curriculum_ids): array
    {
        $list_items = CurriculumItems::query()
                                     ->with(['getObjects','getLearningModule' => function($q) {
                                        $q->with('subject');
                                     }])
                                     ->whereIn('training_program_id',$curriculum_ids)
                                     ->get()
                                     ->toArray();

        return $list_items;
    }

    /**
     * @return array
     */
    public function create($request,$repository): array
    {
        DB::beginTransaction();
        try {
            $check_existed_curriculum = $this->eloquent_model->query()
                                                             ->where('major_id',$request['major_id'])
                                                             ->where('began_date',$request['apply_time'])
                                                             ->get();
            if(!($check_existed_curriculum->isEmpty()))
                throw new \Exception("Khung chương trình đã tồn tại");

            $new_curriculum =$this->eloquent_model->create([
                'major_id' => $request['major_id'],
                'began_date' => $request['apply_time'],
            ]);
            // dd($new_curriculum);

            $major_objects = Major::query()->with('getObjects')
                                           ->where('id',$request['major_id'])
                                           ->get()
                                           ->transform(function ($major) {
                                               return $major->getObjects->map(function ($object) {
                                                   return [$object->shortcode => $object->id];
                                               });
                                           })->first()->toArray();
            $list_learning_modules = $this->getLearningModule(array_filter(array_map(function($learning_module) {
                return $learning_module['learning_module_id'];
            },$request['list_learning_modules'])));
            $process_array = [
                'major_objects'         => $major_objects,
                'list_learning_modules' => $list_learning_modules,
                'new_curriculum'        => $new_curriculum,
            ];
            $insert_records = $this->create_curriculum_service->processDataToCreate($request,$process_array);
            if(isset($insert_records['unique_violated_objects']))
            {
                throw new \Exception('Đối tượng '.$insert_records['unique_violated_objects']. " không thể đăng ký học phần ".$insert_records['learning_module']. "do đã có mặt ở một học phần khác thuộc môn này");
            } 

            CurriculumItems::insert($insert_records);
            DB::commit();
            return [
                'status'    => true,
                'message'   => "Tạo mới thành công khung chương trình"
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'status'    => false,
                'message'   => $e->getMessage()
            ];
        }
    }

    /**
     * @param int $id
     * @param EditCurriculumRequest $request
     * @return array
     */
    public function update(int $id, EditCurriculumRequest $request): array
    {
        try {
            $curriculum = $this->eloquent_model->findOrFail($request->curriculum_id);
            $subject_id =  LearningModule::query()->where('id',$id)->first()->subject_id;

            $curriculum_items = CurriculumItems::query()->with('getObjects')
                                                        ->where('training_program_id',$request->curriculum_id)
                                                        ->where('subject_id',$subject_id)
                                                        ->get();
            
            $enrollment_object = Major::query()->with('getObjects')->where('id',$curriculum['major_id'])
                                               ->get()
                                               ->transform(function ($major) {
                                                    return $major->getObjects->map(function ($object) {
                                                        return [$object->shortcode => $object->id];
                                                    });
                                               })->first()->toArray();

            $existed_objects = $curriculum_items->where('learning_module_id',$id)->pluck('enrollment_object_id')->toArray();
            $duplicate_check_objects = $curriculum_items->where('learning_module_id','!=',$id)->pluck('enrollment_object_id')->toArray();
            // dd($duplicate_check_objects);
            $process_array = [
                'learning_module_id'    => $id,
                'curriculum'            => $curriculum,
                'curriculum_items'      => $curriculum_items,
                'subject_id'            => $subject_id,
                'enrollment_object'     => $enrollment_object,
                'duplicate_check'       => $duplicate_check_objects, 
                'existed_objects'       => $existed_objects
            ];

            $processing_data = $this->update_curriculum_service->processUpdateData($request,$process_array);
            
            if(isset($processing_data['unique_violated_objects']))
            {
                return ['error' => 'Đối tượng '.$processing_data['unique_violated_objects']. " không thể đăng ký 2 học phần trong cùng 1 môn"];
            }
            
            if(!empty($processing_data['insert'])) {
                CurriculumItems::insert($processing_data['insert']);
            }

            if(!empty($processing_data['delete'])) {
                $delete_records = $curriculum_items->filter(function($value,$key) use ($processing_data) {
                    return in_array($value['enrollment_object_id'],$processing_data['delete']);
                })->pluck('id')->toArray();
                CurriculumItems::whereIn('id',$delete_records)->delete();
            }
            
            $return_data = CurriculumItems::query()
                                          ->where('training_program_id',$request->curriculum_id)
                                          ->where('learning_module_id',$request->learning_module_id)
                                          ->get()
                                          ->toArray();
            return $return_data;
            // dd($processing_data['insert']);
        } catch (Exception $e) {
            return (array)$e->getMessage();
        }
    }
        
    /**
     * delete
     *
     * @param  mixed $id
     * @return array
     */
    public function delete($id): array
    {
        try {
            $breakdown_concat_id = explode('_',$id);
            $learning_module_items = CurriculumItems::query()->where('training_program_id',(int)$breakdown_concat_id[1])
                                                             ->where('learning_module_id',(int)$breakdown_concat_id[0])
                                                             ->pluck('id')->toArray();
                                                             
            CurriculumItems::whereIn('id',$learning_module_items)->delete();
            
            return (array)'delete successful';
        } catch (\Exception $e) {
            return (array)$e->getMessage();
        }
    }
    
    public function getMajorObjects(array $major_id): array
    {
        $records = Major::query()->with('getObjects')
                                 ->whereIn('id',$major_id)
                                 ->get()
                                 ->transform(function ($major) {
                                     return [
                                        'id'    => $major->id,
                                        'major' => $major->code,
                                        'objects' => $major->getObjects->map(function ($object) {
                                        return $object->shortcode;
                                    })->toArray()
                                ];
                                 })->toArray();
        return $records;
    }

    public function getLearningModule(array $ids)
    {
        $records = LearningModule::query()
                                 ->with('subject')
                                 ->whereIn('id',$ids)
                                 ->get()
                                 ->transform(function ($learning_module) {
                                     return [
                                        'id'            => $learning_module->id,
                                        'subject_id'    => $learning_module->subject->id ?? $learning_module->alias ?? '',
                                        'subject'       => $learning_module->subject->name ?? $learning_module->alias ?? '',
                                        'code'          => $learning_module->code,
                                        'credits'       => $learning_module->amount_credit
                                     ];
                                 })->toArray();
        return $records;
    }
}