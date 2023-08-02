<?php

class MailShell extends AppShell {

    public $uses = array('Message');

    public function main() {
        $this->out('Searching messages...');
        $messages = $this->Message->find('all',array('conditions' => ['id >=' => 226 ],'limit' => 10));
        foreach ($messages as $message) {
            if ($this->Message->sendMail(unserialize($message['Message']['recipes']),unserialize($message['Message']['subject']),unserialize($message['Message']['message']),unserialize($message['Message']['attachments']),unserialize($message['Message']['template']))) {
                $this->out('Message '.$message['Message']['id'].': Success.');
                $this->Message->id = $message['Message']['id'];
                $this->Message->delete();
            } else {
                $this->out('Message '.$message['Message']['id'].': Fail.');
            }
        }
    }

}
