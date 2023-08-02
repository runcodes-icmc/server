<?php

App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class ArchiveShell extends AppShell {

    public $uses = array('Archive',"Commit","Exercise","ExerciseFile","CompilationFile","ExerciseCaseFile","Offering","ExerciseCase");

    public function main() {

    }

    public function commit () {
        $this->out("<warning>Searching path of commit</warning> #".$this->args[0]);
        $this->out($this->Archive->getCommitFolder($this->args[0]));
    }

    public function output () {
        $this->out("<warning>Searching path of output</warning> #".$this->args[0]);
        $this->out($this->Archive->getOutputFolder($this->args[0]));
    }

    public function exercise () {
        $this->out("<warning>Searching path of files of exercise</warning> #".$this->args[0]);
        $this->out($this->Archive->getExerciseFilesFolder($this->args[0]));
    }

    public function compilation () {
        $this->out("<warning>Searching path of compilation files of exercise</warning> #".$this->args[0]);
        $this->out($this->Archive->getCompilationFilesFolder($this->args[0]));
    }

    public function exercisecase () {
        $this->out("<warning>Searching path of files of exercise case</warning> #".$this->args[0]);
        $this->out($this->Archive->getExerciseCaseFilesFolder($this->args[0]));
    }

    public function copyArchiveCommitFilesToAwsS3 () {
        $this->Commit->recursive = -1;

        $perPage = 1000;
        $i = 1;
        do {
            $this->out("Processing page " . $i);
            $commits = $this->Commit->find("all",array("conditions" => array("aws_key" => null),"fields"=>array("id","exercise_id","commit_time","user_email","hash"),"order" => array("id" => "DESC"),"limit" => $perPage));
            foreach ($commits as $commit) {
                $path = $this->Archive->getCommitFolder($commit["Commit"]["id"]);
                $dir = new Folder($path,false,0777);
                $files=$dir->find();
                if(count($files) > 0) {
                    $file = $path . $files[0];
                    $copy = $this->Archive->copyCommitFiletoAwsS3(
                        array("path" => $path,"name" => $files[0],"size" => filesize($file)),
                        $commit,
                        $this->Exercise->findById($commit["Commit"]["exercise_id"],array("id","offering_id","title"))
                    );
                    if ($copy !== false) {
                        $this->Commit->id=$commit["Commit"]["id"];
                        $this->Commit->saveField("aws_key",$copy);
                        $this->out("Copied: " . $copy);
                    }
//                    sleep(1);
                } else {
                    $this->out("<error>File not found: {$path}</error>");
                }
            }
            $i++;
            sleep(1);
        } while (count($commits) > 0);
        $this->out("<info>Commit Files transport to AWS S3 Finished</info>");
    }

    public function copyOutputFilesToAwsS3 () {
        $perPage = 1000;
        $i = 1;
        do {
            $this->out("Processing page " . $i);
            $commits = $this->Commit->find("all",array("fields"=>array("id","exercise_id","user_email"),"limit" => $perPage,"page" => $i));
            foreach ($commits as $commit) {
                $ret = $this->Archive->copyOutputFileToAwsS3($commit["Commit"]["id"],array("CommitId" => $commit["Commit"]["id"],"ExerciseId" => $commit["Commit"]["exercise_id"],"UserEmail" => $commit["Commit"]["user_email"]));
                if ($ret===-1) {
                    $this->out("<info>Commit " . $commit["Commit"]["id"] . " already in S3</info>");
                } elseif (!$ret) {
                    $this->out("<error>Commit " . $commit["Commit"]["id"] . " failed!</error>");
                }  else {
                    $this->out("<comment>Commit " . $commit["Commit"]["id"] . " uploaded!</comment>");
                }
            }
            $i++;
            sleep(1);
        } while (count($commits) > 0);
        $this->out("<info>Output Files transport to AWS S3 Finished</info>");

    }

    public function copyCasesToAwsS3 () {
        $perPage = 1000;
        $i = 1;
        do {
            $this->out("Processing page " . $i);
            $cases = $this->ExerciseCase->find("all",array("conditions" => array("input_md5" => null),"fields"=>array("id","input","output"),"limit" => $perPage));
            foreach ($cases as $case) {
                $out = "ExerciseCase ". $case["ExerciseCase"]["id"]." ";
                $inputMd5 = md5($case["ExerciseCase"]["input"]);
                $ret = $this->Archive->saveExerciseCaseInputToAwsS3($case["ExerciseCase"]["id"],$case["ExerciseCase"]["input"],array("md5" => $inputMd5));
                if ($ret) {
                    $this->ExerciseCase->id = $case["ExerciseCase"]["id"];
                    $this->ExerciseCase->saveField("input_md5",$inputMd5);
                    $out.="INPUT ";
                }
                $outputMd5 = md5($case["ExerciseCase"]["output"]);
                $ret = $this->Archive->saveExerciseCaseOutputToAwsS3($case["ExerciseCase"]["id"],$case["ExerciseCase"]["output"],array("md5" => $outputMd5));
                if ($ret) {
                    $this->ExerciseCase->id = $case["ExerciseCase"]["id"];
                    $this->ExerciseCase->saveField("output_md5",$outputMd5);
                    $out.="OUTPUT";
                }
                $this->out("<info>" . $out . "</info>");
            }
            $i++;
            sleep(1);
        } while (count($cases) > 0);
        $this->out("<info>Exercise Cases transport to AWS S3 Finished</info>");

    }

    public function copyExerciseFilesToAwsS3 () {
        $perPage = 100;
        $i = 1;
        do {
            $this->out("Processing page " . $i);
            $exerciseFiles = $this->ExerciseFile->find("all",array("limit" => $perPage,"page" => $i));
            foreach ($exerciseFiles as $eF) {
                $exerciseId = $eF["ExerciseFile"]["exercise_id"];
                $this->Exercise->id = $exerciseId;
                $offeringId = $this->Exercise->getOfferingId();
                $this->Offering->id = $offeringId;
                $courseId = $this->Offering->getCourseId();
                $ret = $this->Archive->copyFileToAwsS3(
                    "exercisefiles" . DS . $eF["ExerciseFile"]["exercise_id"],
                    $eF["ExerciseFile"]["path"],
                    Configure::read('Upload.dir').'/exercisefiles/'.$courseId.DS.$offeringId.DS.$exerciseId.DS.$eF["ExerciseFile"]["path"],
                    array("CourseId" => $courseId,"OfferingId" => $offeringId,"ExerciseId" =>$exerciseId,"ExerciseFileId" => $eF["ExerciseFile"]["id"])
                );
                if ($ret === -1) {
                    $this->out("<info>ExerciseFile " .$exerciseId . DS . $eF["ExerciseFile"]["path"] . " already in S3</info>");
                } elseif (!$ret) {
                    $this->out("<error>ExerciseFile " . $exerciseId . DS . $eF["ExerciseFile"]["path"] . " failed!</error>");
                }  else {
                    $this->out("<comment>ExerciseFile " . $exerciseId . DS . $eF["ExerciseFile"]["path"] . " uploaded!</comment>");
                }
            }
            $i++;
            sleep(1);
        } while (count($exerciseFiles) > 0);
        $this->out("<info>Exercise Files transport to AWS S3 Finished</info>");
    }

    public function copyExerciseCaseFilesToAwsS3 () {
        $perPage = 1000;
        $i = 1;
        do {
            $this->out("Processing page " . $i);
            $exerciseFiles = $this->ExerciseCaseFile->find("all",array("limit" => $perPage,"page" => $i));
            foreach ($exerciseFiles as $eF) {
                $exerciseCaseId = $eF["ExerciseCaseFile"]["exercise_case_id"];
                $this->ExerciseCase->id = $exerciseCaseId;
                $exerciseId = $this->ExerciseCase->getExerciseId();
                $this->Exercise->id = $exerciseId;
                $offeringId = $this->Exercise->getOfferingId();
                $this->Offering->id = $offeringId;
                $courseId = $this->Offering->getCourseId();
                $localFile = Configure::read('Upload.dir'). DS . "inputfiles" . DS . $courseId . DS . $offeringId . DS . $exerciseId . DS . $exerciseCaseId . DS . $eF["ExerciseCaseFile"]["path"];
                $ret = $this->Archive->copyExerciseCaseFileToAwsS3(
                    $exerciseCaseId,
                    $eF["ExerciseCaseFile"]["path"],
                    $localFile,
                    array("md5" => md5(file_get_contents($localFile)))
                );
                if ($ret === -1) {
                    $this->out("<info>ExerciseCaseFile " .$exerciseCaseId . DS . $eF["ExerciseCaseFile"]["path"] . " already in S3</info>");
                } elseif (!$ret) {
                    $this->out("<error>ExerciseCaseFile " . $exerciseCaseId . DS . $eF["ExerciseCaseFile"]["path"] . " failed!</error>");
                }  else {
                    $this->out("<comment>ExerciseCaseFile " . $exerciseCaseId . DS . $eF["ExerciseCaseFile"]["path"] . " uploaded!</comment>");
                }
            }
            $i++;
            sleep(1);
        } while (count($exerciseFiles) > 0);
        $this->out("<info>Exercise Case Files transport to AWS S3 Finished</info>");
    }

    public function copyCompilationFilesToAwsS3 () {
        $perPage = 1000;
        $i = 1;
        do {
            $this->out("Processing page " . $i);
            $exerciseFiles = $this->CompilationFile->find("all",array("limit" => $perPage,"page" => $i));
            foreach ($exerciseFiles as $eF) {
                $exerciseId = $eF["CompilationFile"]["exercise_id"];
                $this->Exercise->id = $exerciseId;
                $offeringId = $this->Exercise->getOfferingId();
                $this->Offering->id = $offeringId;
                $courseId = $this->Offering->getCourseId();
                $ret = $this->Archive->copyFileToAwsS3(
                    "compilationfiles" . DS . $eF["CompilationFile"]["exercise_id"],
                    $eF["CompilationFile"]["path"],
                    $this->Archive->getCompilationFilesFolder($eF["CompilationFile"]["exercise_id"]) .$eF["CompilationFile"]["path"],
                    array("CourseId" => $courseId,"OfferingId" => $offeringId,"ExerciseId" =>$exerciseId,"CompilationFileId" => $eF["CompilationFile"]["id"])
                );
                if ($ret === -1) {
                    $this->out("<info>CompilationFile " .$exerciseId . DS . $eF["CompilationFile"]["path"] . " already in S3</info>");
                } elseif (!$ret) {
                    $this->out("<error>CompilationFile " . $exerciseId . DS . $eF["CompilationFile"]["path"] . " failed!</error>");
                }  else {
                    $this->out("<comment>CompilationFile " . $exerciseId . DS . $eF["CompilationFile"]["path"] . " uploaded!</comment>");
                }
            }
            $i++;
            sleep(1);
        } while (count($exerciseFiles) > 0);
        $this->out("<info>Compilation Files transport to AWS S3 Finished</info>");
    }

}