<?php
App::uses('AppModel', 'Model');

class PublicExercise extends AppModel {

    public function getLevels () {
        return array(__("Basic"),__("Easy"),__("Medium"),__("Hard"));
    }

    public function getLevelByIndex ($index) {
        $levels = $this->getLevels();
        return $levels[$index];
    }

}