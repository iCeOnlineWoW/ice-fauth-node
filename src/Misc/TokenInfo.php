<?php

/**
 * Container for token information
 */
class TokenInfo
{
    /** @var int */
    public $id;
    /** @var int */
    public $users_id;
    /** @var array */
    public $services;
    /** @var bool */
    public $valid;
    /** @var string */
    public $token;

    public function __construct($id = 0, $users_id = 0, $services = [], $valid = false, $token = null) {
        $this->id = $id;
        $this->users_id = $users_id;
        $this->services = $services;
        $this->valid = $valid;
        $this->token = $token;
    }
}
