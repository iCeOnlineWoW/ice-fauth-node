<?php

class GuardSQLModel extends GuardBaseModel
{
    public function __construct(array $params)
    {
        parent::__construct($params);

        // TODO
    }

    // GuardBaseModel iface

    public function getFailCountForUsername(string $username): int
    {
        // TODO
        return 0;
    }

    public function getFailCountForIP(string $ip): int
    {
        // TODO
        return 0;
    }

    public function getFailCountForServiceProviderIP(string $ip): int
    {
        // TODO
        return 0;
    }

    public function accumulateFailForUsername(string $username): void
    {
        // TODO
    }

    public function accumulateFailForIP(string $ip): void
    {
        // TODO
    }

    public function accumulateFailForServiceProviderIP(string $ip): void
    {
        // TODO
    }
}
