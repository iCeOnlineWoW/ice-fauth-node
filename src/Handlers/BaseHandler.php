<?php

/**
 * Base class for all handlers
 */
abstract class BaseHandler
{
    /** @var \Monolog\Logger */
    protected $log;
    /** @var \Dibi\Connection */
    protected $db;

    /** @var UserModel */
    private $usersDb = null;
    /** @var AuthModel */
    private $auth = null;

    /**
     * Startup method to be called before every request processing
     */
    public function startup($app)
    {
        $this->log = $app->logger;
        $this->db = $app->db;
    }

    /**
     * Retrieves user model (initializes new if needed)
     * @return UserModel
     */
    protected function users()
    {
        if ($this->usersDb === null)
            $this->usersDb = new UserModel($this->db);
        return $this->usersDb;
    }

    /**
     * Retrieves auth model (initializes new if needed)
     * @return AuthModel
     */
    protected function auth()
    {
        if ($this->auth === null)
            $this->auth = new AuthModel($this->db);
        return $this->auth;
    }
}
