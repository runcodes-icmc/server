<?php

App::uses('AppModel', 'Model');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');


class Lambda extends AppModel {

    public $useTable = false; // This model does not use a database table

    public function __construct($id = false, $table = null, $ds = null) {
        parent::__construct($id, $table, $ds);
        $this->lambdaClient = $this->AwsSDK->createLambda();
    }

    public function runLambdaFunction ($functionName, $payload) {
        $result = $this->lambdaClient->invoke([
                'FunctionName' => $functionName, // REQUIRED
                'InvocationType' => 'Event',
                'LogType' => 'None',
                'Payload' => json_encode($payload),
            ]);
        return ($result["StatusCode"] == 202);
    }



}

