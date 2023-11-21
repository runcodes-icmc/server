<?php
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::uses('CacheEngine', 'Utility');

class MonitorShell extends AppShell {
    public $uses = array('DiskReport','Commit','Message');

    private $moniring = array('xvda1','archive','database');

    public function main() {
        foreach ($this->moniring as $disk) {
            $this->updateDiskData($disk);
        }
        $this->out("<info>Disk Report Finished</info>");

        $this->verifyCommitsStatus();
        $this->out("<info>Commits Verification Finished</info>");
    }

    private function verifyCommitsStatus () {
        $commitsStats = Cache::read('commits_stats');
        if ($commitsStats == false) {
            $commitsStats = array('queue' => 0,'compiling' => 0,'running' => 0, 'busy' => 0);
            $this->out("<warning>Commits stats not cached before</warning>");
        }

        $queue = $this->Commit->find('count',array('conditions' => array('status' => $this->Commit->getInQueueStatusValue())));
        $compiling = $this->Commit->find('count',array('conditions' => array('status' => $this->Commit->getCompilingStatusValue())));
        $running = $this->Commit->find('count',array('conditions' => array('status <' => 4)));
        $busy = $commitsStats['busy'];

        if ($queue == 0 && $compiling == 0 && $running == 0) {
            $busy = 0;
            $this->out("<info>Compiler Empty</info>");
        } else {
            $busy++;

            $this->out("<warning>Compiler not empty</warning>");
        }

        if ($busy > 2) {
            $this->out("<warning>Alert message sent</warning>");
            $msg = "Existem muitos trabalhos em fila no run.codes: <br>{$queue} trabalhos em fila<br>{$compiling} trabalhos em compila&ccedil;&atilde;o<br>{$running} trabalhos n&atilde;o finalizados<br>Verifique o sistema! Um problema pode ter acontecido";
            $this->Message->sendMail("runcodes@icmc.usp.br","Existem muitos trabalhos em fila!",$msg);
        }


        $commitsStats = array('queue' => $queue,'compiling' => $compiling,'running' => $running, 'busy' => $busy);
        Cache::write('commits_stats',$commitsStats);

    }

    private function updateDiskData ($disk) {
        $newReport['DiskReport']['disk'] = $disk;
        $newReport['DiskReport']['datetime'] = date('Y-m-d H:i:s e');
        $newReport['DiskReport']['used'] = $this->getUsed($disk);
        $newReport['DiskReport']['free'] = $this->getFree($disk);
        $newReport['DiskReport']['size'] = $this->getSize($disk);
        $this->DiskReport->create();
        $this->DiskReport->save($newReport,false);
    }

    private function getSize ($disk) {
        $diskSize = exec("df | grep ".$disk." | awk '{print $2}'");
        return floatval($diskSize)/(1024*1024);
    }

    private function getUsed ($disk) {
        $diskSize = exec("df | grep ".$disk." | awk '{print $3}'");
        return floatval($diskSize)/(1024*1024);
    }

    private function getFree ($disk) {
        $diskSize = exec("df | grep ".$disk." | awk '{print $4}'");
        return floatval($diskSize)/(1024*1024);
    }
}