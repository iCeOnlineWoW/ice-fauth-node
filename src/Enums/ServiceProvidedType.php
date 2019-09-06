<?php

/**
 * Service subscription types
 */
class ServiceProvidedType
{
    // indirect = someone (e.g. on remote server) calls API for user;
    // typical scenario: web page that uses its own login form (and maintains its own "bad password" security)
    // we maintain shared service fail count (IP) for such service (limit is high)
    const INDIRECT = "indirect";

    // direct = user itself calls API through a local application
    // typical scenario: an application for mobile phone or desktop application, with built-in login form
    // we maintain individual (user-per-IP) fail count for such service
    const DIRECT = "direct";
}
