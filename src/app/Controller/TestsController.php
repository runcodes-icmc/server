<?php
App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');

class TestsController extends AppController {
    
    public function isAuthorized($user = null) {
        $this->loadModel('User');
        if($user['type'] >= $this->User->getAdminIndex()) return true;
        else return false;
    }
    
    public function performTest() {
        $this->autoRender = false;
//        debug("Test initialized");
//        debug("Adding Users");
//        $this->_addUsers(50);
        $this->_testCommits();
        debug("Test Finished");
    }
    
    private function _addUsers($max = 10) {
        $this->loadModel("Users");
        $this->User->query("DELETE FROM users WHERE name LIKE 'RC User Test%'");
        $this->loadModel("Enrollment");
        $this->loadModel("Commit");
        for ($i = 0; $i < $max; $i++) {
            $user = array();
            $user['User']['name'] = "RC User Test ".$i;
            debug("Adding user".$user['User']['name']);
            $user['User']['email'] = "user_test_".time()."_".$i."@test.run.codes";
            $user['User']['type'] = 0;
            $user['User']['password'] = "@@@35454434@@";
            $user['User']['confirmed'] = true;
            $user['User']['university_id'] = 1;
            $user['User']['source'] = 0;
            $user['User']['identifier'] = time().$i;
            $this->User->create();
            $this->User->save($user);
            
            $enroll = array();
            $enroll['Enrollment']['user_email'] = $user['User']['email'];
            debug("Enrolling user ".$enroll['Enrollment']['user_email']);
            $enroll['Enrollment']['offering_id'] = 5;
            $enroll['Enrollment']['role'] = 0;
            unset($enroll['Enrollment']['id']);
            $this->Enrollment->create();
            if(!$this->Enrollment->save($enroll)) {
                debug($this->Enrollment->validationErrors);
            } else {
                $this->_submitCommit(5, $user['User']['email'], 3, 5);
            }
        }
    }
    
    private function _testCommits() {
        $exercise_id = 8;
        $course_id = 3;
        $offering_id = 5;
        $this->loadModel('Enrollment');
        $enrolls = $this->Enrollment->findAllByOfferingId($offering_id);
        foreach ($enrolls as $e) {
            $this->_submitCommit2($exercise_id, $e['Enrollment']['user_email'], $course_id, $offering_id);
        }
    }
    
    private function _submitCommit($exercise_id, $user_email,$course_id,$offering_id) {
        $this->loadModel('Exercise');
        $this->loadModel('Commit');
        $this->loadModel('Offering');
        
        $commit = array();
        $commit['Commit']['user_email'] = $user_email;
        $commit['Commit']['exercise_id'] = $exercise_id;
        $commit['Commit']['status'] = $this->Commit->getDefaultStatusValue();
        $this->Commit->create();
        $this->Commit->save($commit, false);
        $commit_data = $this->Commit->findById($this->Commit->id);
        $time = $commit_data['Commit']['commit_time'];
        $this->Commit->id = $commit_data['Commit']['id'];
        $upload_dir = new Folder(Configure::read('Upload.dir').'/' . $course_id . '/' . $offering_id . '/' . $exercise_id . '/' . $user_email . '/' . $time . '/', true, 0777);
        $this->Commit->saveField('status', $this->Commit->getInQueueStatusValue());
        $fp = fopen($upload_dir->path.'/11f4e303a1db8f2d8786d414082ef81ef281973e.c', 'w');
        $rand = rand(0, 999);
        $r = $rand % 2;
        if($r == 0) {
            fwrite($fp, '#include<stdio.h>
#include<stdlib.h>
#include<math.h>

int main(int argc, char *argv[]){
	   int a;
	   float b;

	   scanf("%d",&a);
	   b = pow(M_E,a);
		fwrite(&b, sizeof(float), 1, stdout);

	   return 0;
}');
        }  else {
            fwrite($fp, '#include<stdio.h>
#include<stdlib.h>
#include<math.h>

int main(int argc, char *argv[]){
	   int a;
	   float b;

	   scanf("%d",&a);
	   b = pow(2.718,a);
		fwrite(&b, sizeof(float), 1, stdout);

	   return 0;
}');
        }
        fclose($fp);
    }
    
    private function _submitCommit2($exercise_id, $user_email,$course_id,$offering_id) {
        $this->loadModel('Exercise');
        $this->loadModel('Commit');
        $this->loadModel('Offering');
        
        $commit = array();
        $commit['Commit']['user_email'] = $user_email;
        $commit['Commit']['exercise_id'] = $exercise_id;
        $commit['Commit']['status'] = $this->Commit->getDefaultStatusValue();
        $this->Commit->create();
        $this->Commit->save($commit, false);
        $commit_data = $this->Commit->findById($this->Commit->id);
        $time = $commit_data['Commit']['commit_time'];
        $this->Commit->id = $commit_data['Commit']['id'];
        $upload_dir = new Folder(Configure::read('Upload.dir').'/' . $course_id . '/' . $offering_id . '/' . $exercise_id . '/' . $user_email . '/' . $time . '/', true, 0777);
        $this->Commit->saveField('status', $this->Commit->getInQueueStatusValue());
        $rand = rand(0, 999);
        $r = $rand % 3;
        if ($r == 0) {
            copy("http://cloud.fabiosikansi.com/mello.m", $upload_dir->path.'/submittedbytestcontroller.m');
        } else if($r == 1) {
            copy("http://cloud.fabiosikansi.com/test.r", $upload_dir->path.'/submittedbytestcontroller.r');
        } else {
            copy("http://cloud.fabiosikansi.com/test.sce", $upload_dir->path.'/submittedbytestcontroller.sce');
        }
    }
        
}