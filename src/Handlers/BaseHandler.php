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
    /** @var ServiceModel */
    private $servicesDb = null;
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
     * Retrieves services model (initializes new if needed)
     * @return ServiceModel
     */
    protected function services()
    {
        if ($this->servicesDb === null)
            $this->servicesDb = new ServiceModel($this->db);
        return $this->servicesDb;
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

    /**
     * Creates token for given service, replaces existing if needed
     * @param int $users_id
     * @param int $auth_id
     * @param array $services
     * @return TokenInfo
     */
    protected function createToken($users_id, $auth_id, $services): TokenInfo
    {
        // at first, see if there's a token for those services - if yes, invalidate it
        $existing = $this->auth()->getTokenForServices($users_id, $services);
        if ($existing->valid)
            $this->auth()->removeToken($existing->id);

        return $this->auth()->generateToken($auth_id);
    }
}
