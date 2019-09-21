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
    /** @var GuardBaseModel */
    private $guard = null;

    /**
     * Startup method to be called before every request processing
     */
    public function startup($app)
    {
        $this->log = $app->logger;
        $this->db = $app->db;
        $this->guard = $app->guard_model;
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
     * Retrieves guard model created by container
     * @return GuardBaseModel
     */
    protected function guard()
    {
        return $this->guard;
    }

    /**
     * Creates token for given service, replaces existing if needed
     * @param int $users_id
     * @param string $service
     * @return TokenInfo
     */
    protected function createToken($users_id, $service): TokenInfo
    {
        // at first, see if there's a token for the service - if yes, invalidate it
        $existing = $this->auth()->getTokenForService($users_id, $service);
        if ($existing->valid)
            $this->auth()->removeToken($existing->id);

        return $this->auth()->generateToken($users_id, $service);
    }

    /**
     * Retrieves remote IP address; considers the possibility of (r)proxy
     * being on the way
     * @return string
     */
    protected function getRemoteIP(): string
    {
        if (getenv('HTTP_CLIENT_IP'))
            return getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            return getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            return getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            return getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            return getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            return getenv('REMOTE_ADDR');
        return "0.0.0.0";
    }
}
