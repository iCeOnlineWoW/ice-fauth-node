<?php

/**
 * Base class for all models
 */
abstract class BaseModel
{
    /** @var \Dibi\Connection */
    protected $db;

    public function __construct($dbInstance)
    {
        $this->db = $dbInstance;
    }
}
