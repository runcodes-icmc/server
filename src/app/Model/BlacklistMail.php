<?php

App::uses('AppModel', 'Model');

class BlacklistMail extends AppModel {

    public function getTypes () {
        return array(1 => __("Email"),2 => __("Domain"));
    }

    public function addToBlacklist ($email,$type = 1) {
        $newMail = array('BlacklistMail' => array('address' => $email,'type' => $type));
        $this->create();
        return $this->save($newMail);
    }

    public function addDomainToBlacklist ($domain) {
        return $this->addToBlacklist($domain,2);
    }

    public function isBlacklisted ($email) {
        $parts = explode('@',$email);
        if (count($this->findByAddressAndType($email,1)) > 0) {
            return true;
        }
        if (isset($parts[1]) && count($this->findByAddressAndType($parts[1],2)) > 0) {
            return true;
        }
        return false;
    }
}