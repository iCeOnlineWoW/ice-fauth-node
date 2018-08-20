<?php

/**
 * Return codes to be returned from various method and functions
 */
class ReturnCode
{
    // everything ok
    const OK = 'ok';
    // authentication failed (wrong username, password)
    const FAIL_AUTH_FAILED = 'fail_auth_failed';
    // auth info or token expired
    const FAIL_AUTH_EXPIRED = 'fail_auth_expired';
    // auth info is disabled
    const FAIL_AUTH_DISABLED = 'fail_auth_disabled';
}
