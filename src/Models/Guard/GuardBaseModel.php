<?php

abstract class GuardBaseModel
{
    /** @var int maximum number of attempts per username per TTL */
    protected $usernameAttempts = 3;
    /** @var int maximum number of attempts per IP per TTL */
    protected $ipAttempts = 12;
    /** @var int maximum number of attempts per IP of service provider per TTL */
    protected $ipServiceProviderAttempts = 12;

    /** @var int username fail count expires in this many seconds;
     *           default: 1 hour */
    protected $paramUsernameTTL = 3600;

    /** @var int IP fail count expires in this many seconds
     *           default: 1 hour */
    protected $paramIPTTL = 3600;

    /** @var int IP fail count for service providers expires in this many seconds
     *           default: 1 hour */
    protected $paramServiceProviderIPTTL = 3600;

    public function __construct(array $params)
    {
        if (isset($params['username_attempts']) && is_numeric($params['username_attempts']))
            $this->usernameAttempts = $params['username_attempts'];
        if (isset($params['ip_attempts']) && is_numeric($params['ip_attempts']))
            $this->ipAttempts = $params['ip_attempts'];
        if (isset($params['serviceprovider_ip_attempts']) && is_numeric($params['serviceprovider_ip_attempts']))
            $this->ipServiceProviderAttempts = $params['serviceprovider_ip_attempts'];

        if (isset($params['username_ttl']))
            $this->paramUsernameTTL = $params['username_ttl'];
        if (isset($params['ip_ttl']))
            $this->paramIPTTL = $params['ip_ttl'];
        if (isset($params['serviceprovider_ip_ttl']))
            $this->paramServiceProviderIPTTL = $params['serviceprovider_ip_ttl'];
    }

    /**
     * Retrieves the maximum amount of attempts per username per TTL
     * @return int
     */
    public function getMaxUsernameAttempts(): int
    {
        return $this->usernameAttempts;
    }

    /**
     * Retrieves the maximum amount of attempts per IP per TTL
     * @return int
     */
    public function getMaxIPAttempts(): int
    {
        return $this->ipAttempts;
    }

    /**
     * Retrieves the maximum amount of attempts per IP of service provider per TTL
     * @return int
     */
    public function getMaxServiceProviderIPAttempts(): int
    {
        return $this->ipAttempts;
    }

    /**
     * Retrieves authentication fail count for given username
     * @param $username string
     * @return int
     */
    abstract function getFailCountForUsername(string $username): int;

    /**
     * Retrieves authentication fail count for given IP address
     * @param $ip string
     * @return int
     */
    abstract function getFailCountForIP(string $ip): int;

    /**
     * Retrieves authentication fail count for given IP address in service
     * provider context
     * @param $ip string
     * @return int
     */
    abstract function getFailCountForServiceProviderIP(string $ip): int;

    /**
     * Accumulates fail count for given username; if no record found, creates
     * one with fail count equal to 1
     * @param $username string
     */
    abstract function accumulateFailForUsername(string $username): void;

    /**
     * Accumulates fail count for given IP address; if no record found, creates
     * one with fail count equal to 1
     * @param $ip string
     */
    abstract function accumulateFailForIP(string $ip): void;

    /**
     * Accumulates fail count for given IP address in service provider context;
     * if no record found, creates one with fail count equal to 1
     * @param $ip string
     */
    abstract function accumulateFailForServiceProviderIP(string $ip): void;
}
