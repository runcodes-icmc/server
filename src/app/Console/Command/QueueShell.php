<?php

class QueueShell extends AppShell
{

  public $uses = array('Queue', "Message");

  public function main()
  {
    $this->out('Getting messages from queue...');
    $messages = $this->Queue->getNewMessages();
    $this->out(count($messages) . ' Messages read');
    $this->processMessagesQueue($messages);
  }

  private function processMessagesQueue($queue)
  {
    foreach ($queue as $message) {
      $this->mail($message);
    }
  }

  private function mail($message)
  {
    $messageId = $message["id"];
    $this->out('Sending message ID: ' . $messageId);
    $msg = $this->Message->findById($messageId);
    if (count($msg) > 0) {
      if ($this->Message->sendMail(unserialize($msg['Message']['recipes']), unserialize($msg['Message']['subject']), unserialize($msg['Message']['message']), unserialize($msg['Message']['attachments']), unserialize($msg['Message']['template']))) {
        $this->out('Message ' . $msg['Message']['id'] . ': Success.');
        $this->Message->id = $msg['Message']['id'];
        $this->Message->delete();
      } else {
        $this->out('Message ' . $msg['Message']['id'] . ': Fail.');
        Log::register("Fail do send message (Message Id: " . $msg['Message']['id'] . ")");
      }
    } else {
      $this->Queue->clearMessageFromQueue($message);
    }
  }
}
