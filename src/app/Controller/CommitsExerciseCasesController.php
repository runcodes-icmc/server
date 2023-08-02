<?php
App::uses('AppController', 'Controller');

class CommitsExerciseCasesController extends AppController {

    private $studentAuthorized = array('viewuseroutput','viewuseroutputerror','viewcase');
    private $professorAuthorized = array('viewuseroutput','viewuseroutputerror','viewcase');

    public function isAuthorized($user = null) {
        $this->loadModel('User');
        $this->loadModel('Enrollment');
        $this->loadModel('Exercise');
        $this->loadModel('ExerciseCase');
        $this->loadModel('Commit');
        
        if ($user['type'] >= $this->User->getAdminIndex()) {
            return true;
        }
        
        $this->CommitsExerciseCase->id = $this->request->params['pass'][0];
        $commit_id = $this->CommitsExerciseCase->getCommitId();
        $this->Commit->id = $commit_id;
        $exercise_id = $this->Commit->getExerciseId();
        $this->Exercise->id = $exercise_id;
        $offering = $this->Exercise->getOfferingId();
        

        if (in_array(strtolower($this->request->params['action']), $this->professorAuthorized)) {
            if ($this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $offering)) {
                return true;
            }
        }
        else {
            return false;
        }

        $this->Commit->recursive = -1;
        $commit = $this->Commit->findById($commit_id,array('user_email'));
        if (in_array(strtolower($this->request->params['action']), $this->studentAuthorized)) {
            if ($commit['Commit']['user_email'] == $this->currentUser['email']) {
                return true;
            }
        }
        else {
            return false;
        }

    }
    
    public function viewUserOutput($commits_exercise_case_id) {
        if(!$this->CommitsExerciseCase->exists($commits_exercise_case_id)) {
            throw new NotFoundException();
        }
        $memoryStart = memory_get_usage();
        $this->layout = "ajax";

        $options = array('recursive' => -1,'fields' => array('id','exercise_case_id','status','output_type','commit_id'),'conditions' => array('CommitsExerciseCase.' . $this->CommitsExerciseCase->primaryKey => $commits_exercise_case_id));
        $commitsExerciseCase = $this->CommitsExerciseCase->find('first', $options);
        $this->set('commitsExerciseCase', $commitsExerciseCase);
        
    }
    
    public function viewUserOutputError($commits_exercise_case_id) {
        if(!$this->CommitsExerciseCase->exists($commits_exercise_case_id)) {
            throw new NotFoundException();
        }
        $memoryStart = memory_get_usage();
        $this->layout = "ajax";

//        $options = array('recursive' => -1,'fields' => array('id','exercise_case_id','status','output_type','commit_id'),'conditions' => array('CommitsExerciseCase.' . $this->CommitsExerciseCase->primaryKey => $commits_exercise_case_id));
//        $commitsExerciseCase = $this->CommitsExerciseCase->find('first', $options);
        $this->CommitsExerciseCase->id = $commits_exercise_case_id;
        $this->set('commitsExerciseCaseError', $this->CommitsExerciseCase->getErrorMessage());
        
    }

    public function viewCase ($commits_exercise_case_id) {
        if(!$this->CommitsExerciseCase->exists($commits_exercise_case_id)) {
            throw new NotFoundException();
        }
        $this->layout = "ajax";
        $options = array('recursive' => -1,'fields' => array('id','exercise_case_id','status','output_type','commit_id'),'conditions' => array('CommitsExerciseCase.' . $this->CommitsExerciseCase->primaryKey => $commits_exercise_case_id));
        $commitsExerciseCase = $this->CommitsExerciseCase->find('first', $options);
        $this->CommitsExerciseCase->id = $commits_exercise_case_id;
        $commitsExerciseCase['CommitsExerciseCase']['error'] = $this->CommitsExerciseCase->getErrorMessage();

        $this->loadModel("ExerciseCase");
        $this->loadModel("Exercise");
        $this->loadModel("User");
        $this->loadModel("Archive");
        $this->ExerciseCase->recursive = -1;
        $exerciseCase = $this->ExerciseCase->findById($commitsExerciseCase['CommitsExerciseCase']['exercise_case_id'],array("id","input","output","input_md5","output_md5","show_input","show_expected_output","show_user_output"));

        $exercise_id = $this->CommitsExerciseCase->getExerciseId();
        $this->Exercise->id = $exercise_id;
        $offering = $this->Exercise->getOfferingId();
        if (!$this->Enrollment->isEnrolledAsProfessorOrAssistant($this->currentUser['email'], $offering) && !($this->currentUser['type'] >= $this->User->getAdminIndex())) {
            if (!$exerciseCase['ExerciseCase']['show_input']) {
                $exerciseCase['ExerciseCase']['input'] = __("The input is not available for this case");
            } else {
                if (strlen($exerciseCase['ExerciseCase']['input_md5']) > 0) {
                    $exerciseCase['ExerciseCase']['input'] = $this->Archive->getExerciseCaseInputFromAwsS3($exerciseCase['ExerciseCase']['id']);
                }
            }
            if (!$exerciseCase['ExerciseCase']['show_expected_output']) {
                $exerciseCase['ExerciseCase']['output'] = __("The expected output is not available for this case");
            } else {
                if (strlen($exerciseCase['ExerciseCase']['output_md5']) > 0) {
                    $exerciseCase['ExerciseCase']['output'] = $this->Archive->getExerciseCaseOutputFromAwsS3($exerciseCase['ExerciseCase']['id']);
                }
            }
            if (!$exerciseCase['ExerciseCase']['show_user_output']) {
                $commitsExerciseCase['CommitsExerciseCase']['output'] = __("The user output is not available for this case");
            }
        } else {
            if (strlen($exerciseCase['ExerciseCase']['input_md5']) > 0) {
                $exerciseCase['ExerciseCase']['input'] = $this->Archive->getExerciseCaseInputFromAwsS3($exerciseCase['ExerciseCase']['id']);
            }
            if (strlen($exerciseCase['ExerciseCase']['output_md5']) > 0) {
                $exerciseCase['ExerciseCase']['output'] = $this->Archive->getExerciseCaseOutputFromAwsS3($exerciseCase['ExerciseCase']['id']);
            }
        }
        $this->set('commitsExerciseCase', $commitsExerciseCase);
        $this->set('exerciseCase', $exerciseCase);
    }
    
}