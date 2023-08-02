<?php

class SessionShell extends AppShell {

    public function main() {
        $sessionHandler = Configure::read('SessionHandler');
        $sessionHandler->garbageCollect();
    }
}