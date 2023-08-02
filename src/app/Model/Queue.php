<?php
App::uses('AppModel', 'Model');

class Queue extends AppModel {

    public $useTable = false; // This model does not use a database table

    public function __construct ($id = false, $table = null, $ds = null) {
        parent::__construct($id, $table, $ds);
        $queueConfig = [
            'credentials' => [
                'key'    => 'AKIAI4IZ4XF6GF63SQ5A',
                'secret' => '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',
            ]
        ];
        $this->queueClient = $this->AwsSDK->createSQS($queueConfig);
    }

    public function getNewMessages () {
        $result = $this->queueClient->getQueueUrl(['QueueName' => "runcodes-mailing"]);
        $result = $this->queueClient->receiveMessage(['QueueUrl' => $result['QueueUrl'],'MaxNumberOfMessages' => 10]);

        $return = [];
        if (!isset($result['Messages'])) return $return;
        foreach ($result['Messages'] as $message) {
            Log::register("SQS message " . $message['MessageId'] . " read");
            array_push($return,["id" => $message['Body'],"ReceiptHandle" => $message['ReceiptHandle'],"MessageId" => $message['MessageId']]);
        }
        return $return;
    }

    public function addMessageToQueue ($messageId) {
        $result = $this->queueClient->getQueueUrl(['QueueName' => "runcodes-mailing"]);
        $this->queueClient->sendMessage(['QueueUrl' => $result['QueueUrl'],'MessageBody' => $messageId]);
    }

    public function clearMessageFromQueue ($message) {
        $result = $this->queueClient->getQueueUrl(['QueueName' => "runcodes-mailing"]);
        $this->clearFromQueue($result['QueueUrl'],$message['ReceiptHandle']);
        Log::register("SQS message " . $message['MessageId'] . " removed");
    }

    private function clearFromQueue ($queueUrl,$receiptHandle) {
        $this->queueClient->deleteMessage(['QueueUrl' => $queueUrl,'ReceiptHandle' => $receiptHandle]);
    }
}
