<?php
App::uses('AppModel', 'Model');
App::uses('CakeSession', 'Model/Datasource');
/**
 * Commit Model
 *
 * @property User $User
 * @property ExerciseCase $ExerciseCase
 */
class CommitsExerciseCase extends AppModel
{



  public function __construct($id = false, $table = null, $ds = null)
  {
    parent::__construct($id, $table, $ds);
    App::import('Model', 'Archive');
    $this->Archive = new Archive();
  }

  public function getExerciseId()
  {
    $this->recursive = -1;
    App::uses('ExerciseCase', 'Model');
    $exerciseCaseModel = new ExerciseCase();
    $exerciseCaseModel->recursive = -1;
    if (is_numeric($this->id)) {
      $case_ex_data = $this->findById($this->id, array('exercise_case_id'));
      $ex_data = $exerciseCaseModel->findById($case_ex_data['CommitsExerciseCase']['exercise_case_id'], array('exercise_id'));
      return $ex_data['ExerciseCase']['exercise_id'];
    } else {
      return null;
    }
  }

  public function getCommitId()
  {
    $this->recursive = -1;
    if (is_numeric($this->id)) {
      $case_ex_data = $this->findById($this->id, array('commit_id'));
      return $case_ex_data['CommitsExerciseCase']['commit_id'];
    } else {
      return null;
    }
  }

  public function getErrorMessage()
  {
    $case = $this->findById($this->id, array('commit_id', 'exercise_case_id'));
    if (isset($case['CommitsExerciseCase']['commit_id']) && isset($case['CommitsExerciseCase']['exercise_case_id'])) {
      $zip = $this->Archive->getOutputFileFromAwsS3($case['CommitsExerciseCase']['commit_id']);
      if (file_exists($zip)) {
        $file = 'phar://' . $zip . '/' . $case['CommitsExerciseCase']['exercise_case_id'] . '.error';
      } else {
        $file = Configure::read('Upload.dir') . DS . 'outputfiles' . DS . $case['CommitsExerciseCase']['commit_id'] . DS . $case['CommitsExerciseCase']['exercise_case_id'] . '.error';
      }
      clearstatcache();
      if (file_exists($file) && !is_dir($file)) {
        if (filesize($file) < 100000) {
          if (filesize($file) == 0) return __('This commit have not presented any error message');
          return file_get_contents($file);
        } else {
          return __('This commit error output is so big that can not be visualized in browser');
        }
      } else {
        return null;
      }
    }
  }

  public function afterFind($results, $primary = false)
  {

    $loader = new ConfigLoader();
    $domain = $loader->configs['RUNCODES_DOMAIN'];


    foreach ($results as $k => $case) {
      if (isset($case['CommitsExerciseCase']['status_message']) && isset($case['CommitsExerciseCase']['status'])) {
        $signal = str_replace("\n", "", $results[$k]['CommitsExerciseCase']['status_message']);
        if (strpos($signal, "COMMAND") > 0) {
          $notified = CakeSession::read('SignalErrorNotification');
          if (!is_array($notified) || !in_array($results[$k]['CommitsExerciseCase']['commit_id'], $notified)) {
            $notified = array();
            array_push($notified, $results[$k]['CommitsExerciseCase']['commit_id']);
            CakeSession::write('SignalErrorNotification', $notified);
            App::uses('Log', 'Model');
          }
        }
        if ($case['CommitsExerciseCase']['status'] == 2) {
          $results[$k]['CommitsExerciseCase']['status_message'] = __("Bad formatted output");
        } elseif ($case['CommitsExerciseCase']['status'] == 1) {
          if ($signal == "NONZERO") {
            $results[$k]['CommitsExerciseCase']['status_message'] = __("Correct Answer");
          } else {
            $results[$k]['CommitsExerciseCase']['status_message'] = __("Correct Answer");
          }
        } else {
          if ($signal == "SIGKILL" || $signal == "SIGTOUT" || $signal == "SIGXCPU" || $signal == "SIGKILL SIGTOUT") {
            $results[$k]['CommitsExerciseCase']['status_message'] = __("Time Limit Exceeded");
          } elseif ($signal == "NONZERO") {
            $results[$k]['CommitsExerciseCase']['status_message'] = __("Your main function returns a number different from zero");
          } elseif ($signal == "SIGSEGV" || $signal == "SIGBUS") {
            $results[$k]['CommitsExerciseCase']['status_message'] = __("Segmentation Fault");
          } elseif ($signal == "SIGABRT") {
            $results[$k]['CommitsExerciseCase']['status_message'] = __("Aborted");
          } elseif ($signal == "SIGXFSZ") {
            $results[$k]['CommitsExerciseCase']['status_message'] = __("File Size Exceeded");
          } elseif ($signal == "SIGFPE") {
            $results[$k]['CommitsExerciseCase']['status_message'] = __("Floating Point Error");
          } elseif (strlen($signal) == 0) {
            $results[$k]['CommitsExerciseCase']['status_message'] = __("Wrong Answer");
          } else {
            if (strlen($signal) < 9) {
              $notified = CakeSession::read('SignalNotification');
              if (!is_array($notified) || !in_array($signal, $notified)) {
                $notified = array();
                array_push($notified, $signal);
                CakeSession::write('SignalNotification', $notified);
                App::uses('Log', 'Model');
              }
            }
          }
          if (strpos($signal, 'SIGTOUT') !== false &&  strpos($signal, 'SIGKILL') !== false) {
            $results[$k]['CommitsExerciseCase']['status_message'] = __("Aborted Externally");
          }
        }
      }

      if ((isset($case['CommitsExerciseCase']['output']) || isset($case['CommitsExerciseCase']['output_type'])) && isset($case['CommitsExerciseCase']['commit_id']) && isset($case['CommitsExerciseCase']['exercise_case_id'])) {
        clearstatcache();
        $zip = $this->Archive->getOutputFileFromAwsS3($case['CommitsExerciseCase']['commit_id']);
        if (file_exists($zip)) {
          $file = 'phar://' . $zip . '/' . $case['CommitsExerciseCase']['exercise_case_id'] . '.output';
        } else {
          $file = Configure::read('Upload.dir') . DS . 'outputfiles' . DS . $case['CommitsExerciseCase']['commit_id'] . DS . $case['CommitsExerciseCase']['exercise_case_id'] . '.output';
        }
        if (is_file($file)) {
          if (filesize($file) < 100000) {
            $results[$k]['CommitsExerciseCase']['output'] = file_get_contents($file);
          } else {
            $results[$k]['CommitsExerciseCase']['output'] = __('This commit output is so big that can not be visualized in browser');
          }
        } else {
          $notified = CakeSession::read('OutputFileNotFoundNotification');
          if (!is_array($notified) || !in_array($results[$k]['CommitsExerciseCase']['commit_id'], $notified)) {
            $notified = array();
            array_push($notified, $results[$k]['CommitsExerciseCase']['commit_id']);
            CakeSession::write('OutputFileNotFoundNotification', $notified);
            App::uses('Log', 'Model');
          }
        }
      }
    }


    if ((isset($results['CommitsExerciseCase']['output']) || isset($results['CommitsExerciseCase']['output_type'])) && isset($results['CommitsExerciseCase']['commit_id']) && isset($results['CommitsExerciseCase']['exercise_case_id'])) {
      clearstatcache();
      $zip = $this->Archive->getOutputFileFromAwsS3($results['CommitsExerciseCase']['commit_id']);
      if (file_exists($zip)) {
        $file = 'phar://' . $zip . '/' . $results['CommitsExerciseCase']['exercise_case_id'] . '.output';
      } else {
        $file = Configure::read('Upload.dir') . DS . 'outputfiles' . DS . $results['CommitsExerciseCase']['commit_id'] . DS . $results['CommitsExerciseCase']['exercise_case_id'] . '.output';
      }
      if (is_file($file)) {
        if (filesize($file) < 100000) {
          $results['CommitsExerciseCase']['output'] = file_get_contents($file);
        } else {
          $results['CommitsExerciseCase']['output'] = base64_encode(__('This commit output is so big that can not be visualized in browser'));
        }
      } else {
        $notified = CakeSession::read('OutputFileNotFoundNotification');
        if (!is_array($notified) || !in_array($results[$k]['CommitsExerciseCase']['commit_id'], $notified)) {
          $notified = array();
          array_push($notified, $results[$k]['CommitsExerciseCase']['commit_id']);
          CakeSession::write('OutputFileNotFoundNotification', $notified);
          App::uses('Log', 'Model');
        }
      }
    }


    return $results;
  }
}
