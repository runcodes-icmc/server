<?php
App::import('Component','Auth');

class GenerateDataShell extends AppShell {

    public $uses = array('Auth',"Message","User","Enrollment","Exercise","Offering","Commit","Archive");
    public $components = array('Auth');

    public function main() {

        $this->out('Options Available:');
        $this->out('addUsers [number] [delete_before_create=0]');
        $this->out('enrollTestUsers [enroll_at(offeringId)]');
        $this->out('submitCommits [exerciseId] [files_separated_by_comma] [delay=0]');

    }

    public function addUsers() {
        $max = $this->args[0] ? $this->args[0] : 10;
        $deleteBeforeCreate = isset($this->args[1]) ? $this->args[1] : false;

        if ($deleteBeforeCreate) {
            $this->out('<warning>Removing current test users</warning>');
            $this->User->query("DELETE FROM users WHERE name LIKE 'RC User Test%'");
        }

        for ($i = 0; $i < $max; $i++) {
            $user = array();
            $user['User']['name'] = "RC User Test ".$i;
            $this->out("Adding user".$user['User']['name'] . "...");
            $user['User']['email'] = "user_test_".time()."_".$i."@test.run.codes";
            $user['User']['type'] = 0;
            $user['User']['password'] = "@@@35454434@@";
            $user['User']['confirmed'] = true;
            $user['User']['university_id'] = 1;
            $user['User']['source'] = 0;
            $user['User']['identifier'] = time().$i;
            $this->User->create();
            $this->User->save($user);

        }
        $this->out('<info>Process finished!</info>');
    }
    public function enrollTestUsers () {
        $enroll_at = isset($this->args[0]) ? $this->args[0] : null;
        $limit = isset($this->args[1]) ? intval($this->args[1]) : 999999;
        if (!$enroll_at) {
            $this->out('<error>Please determine one offering to enroll each new user!</error>');
            die();
        }
        $this->User->recursive = -1;
        $testUsers = $this->User->find("all",array("conditions" => array("name LIKE" => "RC User Test%"),"limit" => $limit,"fields" => "email"));
        $this->out('<info>Enrolling '.count($testUsers).' users into offering #'.$enroll_at.'</info>');
        foreach ($testUsers as $user) {
            $enroll = array();
            $enroll['Enrollment']['user_email'] = $user['User']['email'];
            debug("Enrolling user ".$enroll['Enrollment']['user_email']);
            $enroll['Enrollment']['offering_id'] = $enroll_at;
            $enroll['Enrollment']['role'] = 0;
            unset($enroll['Enrollment']['id']);
            $this->Enrollment->create();
            if(!$this->Enrollment->save($enroll)) {
                debug($this->Enrollment->validationErrors);
            }
        }
        $this->out('<info>Process finished!</info>');

    }

    public function submitCommits () {

        $exerciseId = $this->args[0] ? $this->args[0] : null;
        $files = isset($this->args[1]) ? $this->args[1] : null;
        $delay = isset($this->args[2]) ? $this->args[2] : 0;
        if (!$exerciseId) {
            $this->out('<error>Please determine the exercise ID!</error>');
            die();
        }
        if (!$files) {
            $this->out('<error>Please determine at least one file!</error>');
            die();
        }
        $this->Exercise->id = $exerciseId;
        $offeringId = $this->Exercise->getOfferingId();
        $enrolled = $this->Enrollment->findAllByOfferingId($offeringId);
        $files = explode(",",$files);
        foreach ($enrolled as $enroll) {
            $this->submitRandomCommit($enroll["Enrollment"]["user_email"],$exerciseId,$files);
            if ($delay > 0) sleep($delay);
        }

    }

    private function submitRandomCommit ($userEmail,$exerciseId,$files) {
        $commit = array();
        $commit['Commit']['user_email'] = $userEmail;
        $commit['Commit']['exercise_id'] = $exerciseId;
        $commit['Commit']['status'] = $this->Commit->getDefaultStatusValue();
        $this->Commit->begin();
        $this->Commit->create();
        $this->Commit->save($commit, false);

        $commitId = $this->Commit->id;
        $hash_time = sha1(time());
        $rand = rand(0,count($files)-1);
        $this->out('<info>Submitting file '.$files[$rand].' as commit #'.$commitId.'</info>');

        $split = explode(".",$files[$rand]);
        $ext = $split[(count($split) - 1) < 0 ? 0 : count($split) - 1];
        $filename = $commitId . "-" .$hash_time . "." . $ext;
        $bucket = Configure::read('AWS.commits-bucket-name');
        if ($ret = $this->Archive->uploadFileToAWS ($files[$rand],$bucket,$exerciseId,$filename)) {
            $this->Commit->saveField("status",$this->Commit->getInQueueStatusValue());
            $this->Commit->saveField("aws_key",$exerciseId . DS . $filename);
            $this->Commit->saveField('hash', $hash_time);
            $this->Commit->commit();
        } else {
            $this->Commit->rollback();
            $this->out('<error>Submit commit #'.$commitId.' failed!</error>');
        }
    }

    public function simulateSubmission () {
        $offeringId = 3;
        $exercises = array();
        //Modelo
        //$exercises[] = array($exerciseId,array("file1","file2",...));

        $numOfSubmissions = rand(0,4);
        $this->out('<info>Submitting '.$numOfSubmissions.' random commits...</info>');

        $users = $this->Enrollment->findAllByOfferingIdAndRole($offeringId,0);
        $totalUsers = count($users);

        while ($numOfSubmissions > 0) {
            $userK = rand(0,$totalUsers);
            $exercise = rand(0,count($exercises));
            $userEmail = $users[$userK]["User"]["email"];
            $exerciseId = $exercises[$exercise][0];
            $files = explode(",",$exercises[$exercise][1]);
            $this->out('<comment>Submitting a random commit of user '.$userEmail.' into exercise #'.$exerciseId.'</comment>');
            $this->submitRandomCommit($userEmail,$exerciseId,$files);
            sleep(rand(5,14));
        }
        $this->out('<info>Process finished!</info>');

    }

}
