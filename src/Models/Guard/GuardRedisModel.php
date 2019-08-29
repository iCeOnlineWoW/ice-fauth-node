<?php

class GuardRedisModel extends GuardBaseModel
{
    private $redis;

    public function __construct(array $params)
    {
        parent::__construct($params);

        $this->redis = new Redis();
        $this->redis->connect($params['host'], $params['port']);

        if (isset($params['password']) && strlen($params['password']) !== 0)
            $this->redis->auth($params['password']);

        if (isset($params['database']) && is_numeric($params['database']))
            $this->redis->select((int)$params['database']);

        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
    }

    /**
     * Retrieves username fail count key
     * @param string $username
     * @return string
     */
    private static function getUsernameFailKey($username): string
    {
        return 'usernamefail:'.strtoupper($username);
    }

    /**
     * Retrieves IP fail count key
     * @param string $ip
     * @return string
     */
    private static function getIPFailKey($ip): string
    {
        // convert IPv6 standard format to dot-separated (colon splits namespaces)
        return 'ipfail:'.str_replace(':', '.', $ip);
    }

    /**
     * Retrieves IP fail count key for service provider context
     * @param string $ip
     * @return string
     */
    private static function getServiceProviderIPFailKey($ip): string
    {
        // convert IPv6 standard format to dot-separated (colon splits namespaces)
        return 'spipfail:'.str_replace(':', '.', $ip);
    }

    // GuardBaseModel iface

    public function getFailCountForUsername(string $username): int
    {
        $failCount = $this->redis->get(self::getUsernameFailKey($username));
        return $failCount ? $failCount : 0;
    }

    public function getFailCountForIP(string $ip): int
    {
        $failCount = $this->redis->get(self::getIPFailKey($ip));
        return $failCount ? $failCount : 0;
    }

    public function getFailCountForServiceProviderIP(string $ip): int
    {
        $failCount = $this->redis->get(self::getServiceProviderIPFailKey($ip));
        return $failCount ? $failCount : 0;
    }
    
    public function accumulateFailForUsername(string $username): void
    {
        $key = self::getUsernameFailKey($username);
        $setExpire = false;

        if (!$this->redis->exists($key))
            $setExpire = true;

        $this->redis->incr($key);

        if ($setExpire)
            $this->redis->expire($key, $this->paramIPTTL);
    }
    
    public function accumulateFailForIP(string $ip): void
    {
        $key = self::getIPFailKey($ip);
        $setExpire = false;

        if (!$this->redis->exists($key))
            $setExpire = true;

        $this->redis->incr($key);

        if ($setExpire)
            $this->redis->expire($key, $this->paramIPTTL);
    }

    public function accumulateFailForServiceProviderIP(string $ip): void
    {
        $key = self::getServiceProviderIPFailKey($ip);
        $setExpire = false;

        if (!$this->redis->exists($key))
            $setExpire = true;

        $this->redis->incr($key);

        if ($setExpire)
            $this->redis->expire($key, $this->paramServiceProviderIPTTL);
    }
}
