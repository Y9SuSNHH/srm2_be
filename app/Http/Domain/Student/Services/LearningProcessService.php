<?php

namespace App\Http\Domain\Student\Services;

use App\Http\Domain\Student\Repositories\LearningProcess\LearningProcessRepositoryInterface;
use App\Http\Domain\Student\Services\StudentService;
use App\Http\Domain\TrainingProgramme\Services\LearningModuleService;
use App\Http\Enum\PetitionStatus;
use Illuminate\Support\Facades\Http;
use App\Http\Domain\TrainingProgramme\Services\StudyPlanService;
use Carbon\Carbon;
use App\Http\Domain\Student\Requests\LearningProcess\SearchRequest;
use App\Http\Domain\Api\Services\StaffService;

class LearningProcessService
{
  /**
   * learning_process_repository
   *
   * @var mixed
   */
  private $learning_process_repository;

  /**
   * __construct
   *
   * @param  mixed $learning_process_repository
   * @return void
   */
  public function __construct(LearningProcessRepositoryInterface $learning_process_repository)
  {
    $this->learning_process_repository = $learning_process_repository;
  }

  /**
   * insert learning process
   *
   * @return void
   */
  public function store()
  {
    //get code and account send lms
    $study_plan_service = app()->service(StudyPlanService::class);
    $param = $study_plan_service->getParams();

    //get list students
    $student_service = app()->service(StudentService::class);
    $list_students = $student_service->getStudents();

    //get list learning modules
    $learning_module_service = app()->service(LearningModuleService::class);
    $list_learning_module = $learning_module_service->getLearningModules();

    //call api login lms
    $wstoken = $this->loginLms();

    if ($wstoken['token']) {
      foreach ($param as $value) {
        call_user_func(function () use ($wstoken, $value, $list_students, $list_learning_module) {
          $value['wsfunction'] = 'local_core_get_usergrades_incourse';
          $value['wstoken'] = $wstoken['token'];
          $value['moodlewsrestformat'] = 'json';
          //call api get grades from lms
          $response = $this->getGrades($value);

          $learning_process = array();
          if ($response['success'] == true) {
            $item = array();
            foreach ($response['data'] as $data) {
              // $item['student_id'] = $list_students->where('account', $data['account'])->first() ? $list_students->where('account', $data['account'])->first()->id : null;
              $item['student_id'] = $this->findId($list_students, 'account', $data['account']) ?? null;
              if ($data['subjectscore']) {
                foreach ($data['subjectscore'] as $subject) {
                  // $item['learning_modules_id'] = $list_learning_module->where('code', $subject['learning_module_code'])->first() ? $list_learning_module->where('code', $subject['learning_module_code'])->first()->id : null;
                  // $item['learning_modules_id'] = $this->findId($list_learning_module, 'code', $subject['learning_module_code']) ?? null;
                  foreach ($list_learning_module as $learning_module) {
                    if ($learning_module->code == $subject['learning_module_code']) {
                      $item['learning_modules_id'] = $learning_module->id;
                    }
                  }
                  if ($item['student_id'] && $item['learning_modules_id'] && $subject['item_type']) {
                    $item['result_btgk1'] = $subject['diem_btgk1'] ?? 'null';
                    $item['result_btgk2'] = $subject['diem_btgk2'] ?? 'null';
                    $item['result_diem_cc'] = $subject['diem_cc'] ?? 'null';
                    $item['deadline_btgk1'] = $subject['deadline_btgk1'] ? "'" . $subject['deadline_btgk1'] . "'"  : 'null';
                    $item['deadline_btgk2'] = $subject['deadline_btgk2'] ? "'" . $subject['deadline_btgk2'] . "'"  : 'null';
                    $item['deadline_diem_cc'] = $subject['deadline_cc'] ? "'" . $subject['deadline_cc'] . "'" : 'null';
                    $item['item_type'] = $subject['item_type'] ? "'" . $subject['item_type'] . "'" : 'null';
                    $learning_process[] = $item;
                  }
                }
              }
            }

            $values = "(" . implode('), (', array_map(function ($array) {
              return implode(', ', $array);
            }, $learning_process)) . ")";
            $query = "insert into learning_processes (student_id, learning_modules_id, result_btgk1, result_btgk2, 
            result_diem_cc, deadline_btgk1, deadline_btgk2, deadline_diem_cc, item_type) 
            values $values 
            on conflict (student_id, learning_modules_id, item_type) 
            do update set " .
              'learning_modules_id=excluded.learning_modules_id,' .
              'student_id=excluded.student_id,' .
              'result_btgk1=excluded.result_btgk1,' .
              'result_btgk2=excluded.result_btgk2,' .
              'result_diem_cc=excluded.result_diem_cc,' .
              'deadline_btgk1=excluded.deadline_btgk1,' .
              'deadline_btgk2=excluded.deadline_btgk2, ' .
              'deadline_diem_cc=excluded.deadline_diem_cc, ' .
              'item_type=excluded.item_type ';

            $this->learning_process_repository->insert($query);
          }
        }, sleep(5));
      }
    }
  }

  /**
   * findId
   *
   * @param  mixed $array
   * @param  mixed $key_find
   * @param  mixed $param
   * @return void
   */
  public function findId($array, $key_find, $param)
  {
    foreach ($array as $arr) {
      if ($arr[$key_find] == $param) {
        return $arr['id'];
      }
    }
  }


  /**
   * loginLms
   *
   * @return void
   */
  public function loginLms()
  {
    $url = env('LMS_DOMAIN') . '/login/token.php';
    $param = [
      'username' => env('USERNAME_LOGIN_LMS'),
      'password' => env('PASSWORD_LOGIN_LMS'),
      'service' => env('SERVICE_LOGIN_LMS')
    ];
    $response = Http::asForm()->post($url, $param);
    return $response->json();
  }

  /**
   * getGrades
   *
   * @param  mixed $param
   * @return void
   */
  public function getGrades($param)
  {
    $url = env('LMS_DOMAIN') . '/webservice/rest/server.php';
    $response = Http::asForm()->post($url, $param);
    return $response->json();
  }

  public function listLearningProcess(SearchRequest $request)
  {
    //get all data student care
    $data = $this->learning_process_repository->getAll($request);

    $data->getCollection()->transform(function ($student) {
      $now = Carbon::now();
      $student_classroom_id = $student->id;
      $student_id = $student->student->id;
      $student_profile_id = $student->student->studentProfile->id;
      $profile_code = $student->student->studentProfile->profile_code ?? null;
      $student_code = $student->student->student_code ?? null;
      $student_name = $student->student->getProfile->firstname . ' ' . $student->student->getProfile->lastname;
      $classroom_code = $student->getClassroom->code ?? null;
      $student_status = $student->student->student_status ?? null;
      $study_plans = $student->studyPlans;
      $receivable_collect = $student->student->studentProfile->receivable;
      $amount_receive_by_code = $this->learning_process_repository->getAmountsReceived($profile_code);
      $amount_receive_by_code_collect = $amount_receive_by_code->amountsReceived ?? [];
      $petition_collect = $student->student->petitions;
      $petition_noprocess = $petition_collect->where('status', PetitionStatus::LEARNING_MANAGEMENT_SEND)->count();
      $petition_processing = $petition_collect->whereIn('status', [PetitionStatus::ACADEMIC_AFFAIR_ACCEPT, PetitionStatus::ACADEMIC_AFFAIR_SEND])->count();
      $petition_processed = $petition_collect->whereNotIn('status', [PetitionStatus::LEARNING_MANAGEMENT_SEND, PetitionStatus::ACADEMIC_AFFAIR_ACCEPT, PetitionStatus::ACADEMIC_AFFAIR_SEND])->count();
      $care_history = $student->student->careHistories;
      $list_learning = array();

      if ($study_plans) {
        foreach ($study_plans as $key => $study) {
          if ($key == 0) {
            $semester = $study->semester;
            if ($semester == 1) {
              $deadline_receivable = Carbon::parse($study->study_began_date);
            } else {
              $deadline_receivable = Carbon::parse($study->study_began_date)->subDays(6);
            }
            $receivable = $receivable_collect->where('learning_wave_number', $semester) ? $receivable_collect->where('learning_wave_number', $semester)->sum('receivable') : null;
            $amount_receive = $amount_receive_by_code_collect->where('dot_hoc_so', $semester) ? (int)$amount_receive_by_code_collect->where('dot_hoc_so', $semester)->sum('thuc_nop') : null;
          }
          if ($study->learningModule) {
            $val['subject_name'] = $study->learningModule->subject->name;
            $learning_process = $study->learningProcess->where('student_id', $student->student_id);
            if (count($learning_process) > 0) {
              foreach ($learning_process as $key => $learning) {
                $deadline_btgk1 = $learning['deadline_btgk1'];
                $deadline_btgk2 = $learning['deadline_btgk2'];
                $deadline_diem_cc = $learning['deadline_diem_cc'];
                $val['learning_process']['id'] = $learning['id'];
                $val['learning_process']['learning_modules_id'] = $learning['learning_modules_id'];
                $val['learning_process']['student_id'] = $learning['student_id'];
                $val['learning_process']['result_btgk1'] = $learning['result_btgk1'];
                $val['learning_process']['result_btgk2'] = $learning['result_btgk2'];
                $val['learning_process']['result_diem_cc'] = $learning['result_diem_cc'];
                $val['learning_process']['deadline_btgk1'] = $learning['deadline_btgk1'];
                $val['learning_process']['deadline_btgk2'] = $learning['deadline_btgk2'];
                $val['learning_process']['deadline_diem_cc'] = $learning['deadline_diem_cc'];
                $val['learning_process']['item_type'] = $learning['item_type'];
                if ($learning['deadline_btgk1']) {
                  if (($now <= Carbon::parse($learning['deadline_btgk1'])->subDays(7)) ||
                    ($now < Carbon::parse($learning['deadline_btgk1'])->subDays(5)) ||
                    (Carbon::parse($learning['deadline_btgk1']) >= $now && $learning['result_btgk1']) ||
                    (Carbon::parse($learning['deadline_btgk1']) <= $now && $learning['result_btgk1'])
                  ) {
                    $val['learning_process']['btgk1_status'] = 'completed';
                  } else if (Carbon::parse($learning['deadline_btgk1'])->subDays(5) <= $now && $now < Carbon::parse($learning['deadline_btgk1'])->subDays(1) && !$learning['result_btgk1']) {
                    $val['learning_process']['btgk1_status'] = 'incompleted';
                  } else if (Carbon::parse($learning['deadline_btgk1'])->subDays(1) <= $now && $now <= Carbon::parse($learning['deadline_btgk1']) && !$learning['result_btgk1']) {
                    $val['learning_process']['btgk1_status'] = 'notcompleted';
                  } else {
                    $val['learning_process']['btgk1_status'] = 'notcompleted';
                  }
                } else {
                  $val['learning_process']['btgk1_status'] = 'completed';
                }

                if ($learning['deadline_btgk2']) {
                  if (($now <= Carbon::parse($learning['deadline_btgk2'])->subDays(7)) ||
                    ($now < Carbon::parse($learning['deadline_btgk2'])->subDays(5)) ||
                    (Carbon::parse($learning['deadline_btgk2']) >= $now && $learning['result_btgk2']) ||
                    (Carbon::parse($learning['deadline_btgk2']) <= $now && $learning['result_btgk2'])
                  ) {
                    $val['learning_process']['btgk2_status'] = 'completed';
                  } else if (Carbon::parse($learning['deadline_btgk2'])->subDays(5) <= $now && $now < Carbon::parse($learning['deadline_btgk2'])->subDays(1) && !$learning['result_btgk2']) {
                    $val['learning_process']['btgk2_status'] = 'incompleted';
                  } else if (Carbon::parse($learning['deadline_btgk2'])->subDays(1) <= $now && $now <= Carbon::parse($learning['deadline_btgk2']) && !$learning['result_btgk2']) {
                    $val['learning_process']['btgk2_status'] = 'notcompleted';
                  } else {
                    $val['learning_process']['btgk2_status'] = 'notcompleted';
                  }
                } else {
                  $val['learning_process']['btgk2_status'] = 'completed';
                }

                if ($learning['deadline_diem_cc']) {
                  if ((Carbon::parse($learning['deadline_diem_cc'])->subDays(7) >= $now) ||
                    ($now < Carbon::parse($learning['deadline_diem_cc'])->subDays(5)) ||
                    (Carbon::parse($learning['deadline_diem_cc']) >= $now && $learning['result_diem_cc']) ||
                    (Carbon::parse($learning['deadline_diem_cc']) <= $now && $learning['result_diem_cc'])
                  ) {
                    $val['learning_process']['diem_cc_status'] = 'completed';
                  } else if (Carbon::parse($learning['deadline_diem_cc'])->subDays(5) <= $now && $now < Carbon::parse($learning['deadline_diem_cc'])->subDays(1) && !$learning['result_diem_cc']) {
                    $val['learning_process']['diem_cc_status'] = 'incompleted';
                  } else if (Carbon::parse($learning['deadline_diem_cc'])->subDays(1) <= $now && $now <= Carbon::parse($learning['deadline_diem_cc']) && !$learning['result_diem_cc']) {
                    $val['learning_process']['diem_cc_status'] = 'notcompleted';
                  } else {
                    $val['learning_process']['diem_cc_status'] = 'notcompleted';
                  }
                } else {
                  $val['learning_process']['diem_cc_status'] = 'completed';
                }

                $list_learning[] = $val;
              }
            } else {
              $val['learning_process']['btgk1_status'] = 'notcompleted';
              $val['learning_process']['btgk2_status'] = 'notcompleted';
              $val['learning_process']['diem_cc_status'] = 'notcompleted';
              $list_learning[] = $val;
            }
          }
        }

        $list_learning = collect($list_learning);
        if (count($list_learning->where('learning_process.btgk1_status', 'notcompleted')) > 0) {
          $btgk1_status = 'notcompleted';
        } else if (count($list_learning->where('learning_process.btgk1_status', 'incompleted')) > 0) {
          $btgk1_status = 'incompleted';
        } else if (count($list_learning->where('learning_process.btgk1_status', 'completed')) > 0) {
          $btgk1_status = 'completed';
        }

        if (count($list_learning->where('learning_process.btgk2_status', 'notcompleted')) > 0) {
          $btgk2_status = 'notcompleted';
        } else if (count($list_learning->where('learning_process.btgk2_status', 'incompleted')) > 0) {
          $btgk2_status = 'incompleted';
        } else if (count($list_learning->where('learning_process.btgk2_status', 'completed')) > 0) {
          $btgk2_status = 'completed';
        }

        if (count($list_learning->where('learning_process.diem_cc_status', 'notcompleted')) > 0) {
          $diem_cc_status = 'notcompleted';
        } else if (count($list_learning->where('learning_process.diem_cc_status', 'incompleted')) > 0) {
          $diem_cc_status = 'incompleted';
        } else if (count($list_learning->where('learning_process.diem_cc_status', 'completed')) > 0) {
          $diem_cc_status = 'completed';
        }
      }

      $staff = app()->service(StaffService::class)->getStaffInfo();

      $care_history->map(function ($care_history) use ($staff) {
        $care_history['fullname_created_by'] = $care_history->created_by ? ($staff->where('user_id', $care_history->created_by)->first() ? $staff->where('user_id', $care_history->created_by)->first()->fullname : null) : null;
        $care_history['fullname_updated_by'] = $care_history->updated_by ? ($staff->where('user_id', $care_history->updated_by)->first() ? $staff->where('user_id', $care_history->updated_by)->first()->fullname : null) : null;
      });

      return  [
        'id' => $student_classroom_id ?? null,
        'student_profile_id' => $student_profile_id ?? null,
        'profile_code' => $profile_code ?? null,
        'student_id' => $student_id ?? null,
        'student_code' => $student_code ?? null,
        'student_name' => $student_name ?? null,
        'classroom_code' => $classroom_code ?? null,
        'student_status' => $student_status ?? null,
        'semester' => $semester ?? null,
        'deadline_btgk1' => $deadline_btgk1 ?? null,
        'deadline_btgk2' => $deadline_btgk2 ?? null,
        'deadline_diem_cc' => $deadline_diem_cc ?? null,
        'btgk1_status' => $btgk1_status ?? null,
        'btgk2_status' => $btgk2_status ?? null,
        'diem_cc_status' => $diem_cc_status ?? null,
        'deadline_receivable' => $deadline_receivable ?? null,
        'receivable' => $receivable ?? 0,
        'amount_receive' => $amount_receive ?? 0,
        'petition_noprocess' => $petition_noprocess ?? 0,
        'petition_processing' => $petition_processing ?? 0,
        'petition_processed' => $petition_processed ?? 0,
        'study_plans' => $list_learning,
        'care_history' => $care_history,
      ];
    });

    return $data;
  }

  public function insertLearningProcess($param)
  {
    $student_service = app()->service(StudentService::class);
    $list_students = $student_service->getStudents();

    //get list learning modules
    $learning_module_service = app()->service(LearningModuleService::class);
    $list_learning_module = $learning_module_service->getLearningModules();

    //call api login lms
    $wstoken = $this->loginLms();
    if ($wstoken['token']) {
      $value['wsfunction'] = 'local_core_get_usergrades_incourse';
      $value['wstoken'] = $wstoken['token'];
      $value['moodlewsrestformat'] = 'json';
      $value['learning_module_code'][] = "470058";
      $value['account'][] = "changnv040995";
      //call api get grades from lms
      $response = $this->getGrades($value);
      $learning_process = array();
      if ($response['success'] == true) {
        $item = array();
        foreach ($response['data'] as $data) {
          $item['student_id'] = $this->findId($list_students, 'account', $data['account']) ?? null;
          if ($data['subjectscore']) {
            foreach ($data['subjectscore'] as $subject) {
              foreach ($list_learning_module as $learning_module) {
                if ($learning_module->code == $subject['learning_module_code']) {
                  $item['learning_modules_id'] = $learning_module->id;
                }
              }
              if ($item['student_id'] && $item['learning_modules_id'] && $subject['item_type']) {
                $item['result_btgk1'] = $subject['diem_btgk1'] ?? 'null';
                $item['result_btgk2'] = $subject['diem_btgk2'] ?? 'null';
                $item['result_diem_cc'] = $subject['diem_cc'] ?? 'null';
                $item['deadline_btgk1'] = $subject['deadline_btgk1'] ? "'" . $subject['deadline_btgk1'] . "'"  : 'null';
                $item['deadline_btgk2'] = $subject['deadline_btgk2'] ? "'" . $subject['deadline_btgk2'] . "'"  : 'null';
                $item['deadline_diem_cc'] = $subject['deadline_cc'] ? "'" . $subject['deadline_cc'] . "'" : 'null';
                $item['item_type'] = $subject['item_type'] ? "'" . $subject['item_type'] . "'" : 'null';
                $learning_process[] = $item;
              }
            }
          }
        }

        $values = "(" . implode('), (', array_map(function ($array) {
          return implode(', ', $array);
        }, $learning_process)) . ")";
        $query = "insert into learning_processes (student_id, learning_modules_id, result_btgk1, result_btgk2, 
          result_diem_cc, deadline_btgk1, deadline_btgk2, deadline_diem_cc, item_type) 
          values $values 
          on conflict (student_id, learning_modules_id, item_type) 
          do update set " .
          'learning_modules_id=excluded.learning_modules_id,' .
          'student_id=excluded.student_id,' .
          'result_btgk1=excluded.result_btgk1,' .
          'result_btgk2=excluded.result_btgk2,' .
          'result_diem_cc=excluded.result_diem_cc,' .
          'deadline_btgk1=excluded.deadline_btgk1,' .
          'deadline_btgk2=excluded.deadline_btgk2, ' .
          'deadline_diem_cc=excluded.deadline_diem_cc, ' .
          'item_type=excluded.item_type ';
        $this->learning_process_repository->insert($query);
      }
    }
  }
}
