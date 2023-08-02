<?php
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class BackupShell extends AppShell {

    public $uses = array('Archive','Commit');

    public function main() {
        $date = new DateTime();
        $date->sub(new DateInterval('P1D'));
        $yesterday = $date->format('Y-m-d');

        $this->out(date('D/M/Y H:i:s')." <error>Starting Backup...</error>");
        $this->out("<comment>---------------------------------------------------------------</comment>");
//        $this->database();
        $this->out("<comment>---------------------------------------------------------------</comment>");
//        $this->commits($yesterday);
        $this->out("<comment>---------------------------------------------------------------</comment>");
//        $this->exerciseFiles();
        $this->out("<comment>---------------------------------------------------------------</comment>");
//        $this->compilationFiles();
        $this->out("<comment>---------------------------------------------------------------</comment>");
//        $this->inputFiles();
        $this->out("<comment>---------------------------------------------------------------</comment>");
        $this->out(date('D/M/Y H:i:s')." <error>Backup Finished</error>");
    }
    //DEPRECATED
    public function database () {
//        $this->out(date('D/M/Y H:i:s')." <warning>Performing database dump...</warning>");
//        exec("/usr/bin/pg_dump runcodes -n public -U runcodesbackup -w -f /tmp/dump.sql");
//        $this->out(date('D/M/Y H:i:s')." <warning>Database dump completed</warning>");
//
//        if (file_exists("/tmp/dump.sql")) {
//            exec("/usr/bin/zip -rq /tmp/database.".strtolower(date('l')).".zip /tmp/dump.sql");
//        }
//        $this->sendToAmazonS3("runcodesdatabases","","/tmp/database.".strtolower(date('l')).".zip","database.".strtolower(date('l')).".zip");
//        $this->removeFile("/tmp/dump.sql");
//        $this->removeFile("/tmp/database.".strtolower(date('l')).".zip");
    }

    public function commits ($date = null) {

        if (is_null($date) && isset($this->args[0])) {
            $date = $this->args[0];
        }
        if (is_null($date)) {
            $this->out(date('D/M/Y H:i:s')." <warning>Searching for commits...</warning>");
            $zipname = "commits.zip";
            $commits = $this->Commit->find('all',array('fields' => array('id')));
        } else {
            $this->out(date('D/M/Y H:i:s')." <warning>Searching for commits on ".$date."...</warning>");
            $zipname = "commits.".$date.".zip";
            $commits = $this->Commit->find('all',array('conditions' => array('commit_time >= ' => $date." 00:00:00",'commit_time <= ' => $date." 23:59:59"),'fields' => array('id')));
        }
        $zip = "/usr/bin/zip -q /tmp/".$zipname." ";
        $countFiles = 0;
        foreach ($commits as $commit) {
            $dirname = $this->Archive->getCommitFolder($commit['Commit']['id']);
            $dir = new Folder($dirname,false,0777);
            $files=$dir->find();
            if (count($files) > 0) {
                foreach ($files as $f) {
                    $zip.= str_replace(':','\:',str_replace(' ','\ ',$dirname)) . $f." ";
                    $countFiles++;
                }
            }
        }
        $this->out(date('D/M/Y H:i:s')." <info>".$countFiles." files found.</info>");
        if ($countFiles > 0) {
            $this->out(exec($zip));
            $this->sendToAmazonS3("runcodesarchive","commits","/tmp/".$zipname,$zipname);
            $this->removeFile("/tmp/".$zipname);
        }

    }

    public function exerciseFiles () {
        $this->out(date('D/M/Y H:i:s')." <warning>Copying Exercise Files...</warning>");
        exec("/usr/bin/zip -rq /tmp/exercisefiles.zip /archive/exercisefiles/ -x /archive/exercisefiles/tmp/");
        $this->sendToAmazonS3("runcodesarchive","","/tmp/exercisefiles.zip","exercisefiles.zip");
        $this->removeFile("/tmp/exercisefiles.zip");
    }

    public function inputFiles () {
        $this->out(date('D/M/Y H:i:s')." <warning>Copying Input Files...</warning>");
        exec("/usr/bin/zip -rq /tmp/inputfiles.zip /archive/inputfiles/ -x /archive/inputfiles/tmp/");
        $this->sendToAmazonS3("runcodesarchive","","/tmp/inputfiles.zip","inputfiles.zip");
        $this->removeFile("/tmp/inputfiles.zip");
    }

    public function compilationFiles () {
        $this->out(date('D/M/Y H:i:s')." <warning>Copying Compilation Files...</warning>");
        exec("/usr/bin/zip -rq /tmp/compilationfiles.zip /archive/compilationfiles/ -x /archive/compilationfiles/tmp/");
        $this->sendToAmazonS3("runcodesarchive","","/tmp/compilationfiles.zip","compilationfiles.zip");
        $this->removeFile("/tmp/compilationfiles.zip");
    }

    private function sendToAmazonS3 ($bucket,$bucketfolder,$file,$filename){
        $this->out(date('D/M/Y H:i:s')." <warning>Uploading file '".$file."' to Amazon S3</warning>");
        if (strlen($bucketfolder) > 0) {
            $uploadCommand = "/usr/local/bin/aws s3 cp ".$file." s3://".$bucket."/".$bucketfolder."/".$filename."";
        } else {
            $uploadCommand = "/usr/local/bin/aws s3 cp ".$file." s3://".$bucket."/".$filename."";
        }
        $output = exec($uploadCommand);
        $this->out($output);
        $this->out(date('D/M/Y H:i:s')." <warning>Upload finished</warning>");
    }

    private function removeFile ($file) {
        exec("rm ".$file);
    }

}