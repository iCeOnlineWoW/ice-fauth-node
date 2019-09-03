<?php

/**
 * Model for maintaining authentication and autorization procedures
 */
class AuthModel extends BaseModel
{
    // password security options for all hashing; may be modified at any time, passwords would be rehashed on each verify call if needed
    const PASS_HASH_OPTIONS = [ 'cost' => 1 ];
    // default token validity time in seconds
    const TOKEN_VALIDITY_DEFAULT = 3600; // 1 hour
    // minimum validity time allowed for a token
    const TOKEN_VALIDITY_MIN = 60; // 60 seconds
    // maximum validity time allowed for a token
    const TOKEN_VALIDITY_MAX = 2592000; // 1 month
    // how many bytes should a token contain? This is not the length of hex string!
    const TOKEN_BYTE_LENGTH = 64;

    // minimum password length
    const PASS_MIN_LENGTH = 6;

    public function __construct($dbInstance)
    {
        parent::__construct($dbInstance);
    }

    /**
     * Checks for password security properties, and returns a value from 0 to 3
     * @param string $password
     * @return int
     */
    public static function checkPasswordPolicy($password): int
    {
        if (strlen($password) < self::PASS_MIN_LENGTH)
            return PasswordSecureLevel::NOT_SECURE;

        $basic = false;     // a-z, space, tab
        $upcase = false;    // A-Z
        $num = false;       // 0-9
        $specials = false;  // any other character
        for ($i = 0; $i < strlen($password); $i++)
        {
            $chr = ord($password[$i]);
            if (($chr >= ord('a') && $chr <= ord('z')) || $chr == ord(' ') || $chr == ord('\t'))
                $basic = true;
            else if ($chr >= ord('A') && $chr <= ord('Z'))
                $upcase = true;
            else if ($chr >= ord('0') && $chr <= ord('9'))
                $num = true;
            else
                $specials = true;
        }

        $numClasses = (int)$basic + (int)$upcase + (int)$num + (int)$specials;
        if ($numClasses === 1)
            return PasswordSecureLevel::MINIMAL;
        if ($numClasses === 2 || $numClasses === 3)
            return PasswordSecureLevel::NORMAL;
        if ($numClasses === 4)
            return PasswordSecureLevel::SECURE;

        // just for case - it should not happen, but in case of future modifications...
        return PasswordSecureLevel::NOT_SECURE;
    }

    /**
     * Verifies the validity of auth info or auth token
     * @param Dibi\Row $row
     * @return bool
     */
    private function isAuthDateValid($row): bool
    {
        $dateNow = new Dibi\DateTime();
        return ($row['valid_from'] === null || $dateNow > $row['valid_from']) &&
               ($row['valid_to'] === null || $dateNow < $row['valid_to']);
    }

    /**
     * Validates given password for given user
     * @param int $users_id
     * @param string $value
     * @param int $id
     * @param array $services
     * @return string
     */
    public function validatePasswordAuth($users_id, $value, &$id, &$services): string
    {
        $rec = $this->db->query("SELECT * FROM user_auth_info WHERE users_id = ? AND type = ?", $users_id, AuthType::PASSWORD)->fetch();
        if (!$rec)
            return ReturnCode::FAIL_AUTH_FAILED;
        if ($rec['disabled'])
            return ReturnCode::FAIL_AUTH_DISABLED;

        $dbHash = $rec['value'];

        if (!password_verify($value, $dbHash))
            return ReturnCode::FAIL_AUTH_FAILED;

        if (!$this->isAuthDateValid($rec))
            return ReturnCode::FAIL_AUTH_EXPIRED;

        // check if password needs rehashing - this is needed in case of security parameters change
        // e.g. when we upgrade PHP, and for some reason, bcrypt would not be considered secure, and
        // developers decide to use argon2 instead, so we need to rehash the password and store new hash
        if (password_needs_rehash($dbHash, PASSWORD_DEFAULT, self::PASS_HASH_OPTIONS))
        {
            $newHash = password_hash($value, PASSWORD_DEFAULT, self::PASS_HASH_OPTIONS);
            $this->db->query("UPDATE user_auth_info SET value = ? WHERE id = ?", $newHash, $rec['id']);
        }

        // return id and services array
        $id = $rec['id'];
        $services = unserialize($rec['services']);

        return ReturnCode::OK;
    }

    /**
     * Adds a new password auth info for given user
     * @param int $users_id ID of user
     * @param string $value password
     * @param array $services array of services
     * @param int $valid_from unix timestamp of validity start
     * @param int $valid_to unix timestamp of validity end
     * @param bool $disabled is this auth entry disabled?
     * @return bool
     */
    public function addPasswordAuth($users_id, $value, $services, $valid_from = null, $valid_to = null, $disabled = false): bool
    {
        // require at least level 1
        if (self::checkPasswordPolicy($value) === PasswordSecureLevel::NOT_SECURE)
            return false;

        $this->db->query("INSERT INTO user_auth_info", [
            'users_id' => $users_id,
            'type' => AuthType::PASSWORD,
            'value' => password_hash($value, PASSWORD_DEFAULT, self::PASS_HASH_OPTIONS),
            'services' => serialize($services),
            'valid_from' => $valid_from ? (new DateTime())->setTimestamp($valid_from) : null,
            'valid_to' => $valid_to ? (new DateTime())->setTimestamp($valid_to) : null,
            'disabled' => $disabled
        ]);

        return true;
    }

    /**
     * Subscribes user auth info to a given service
     * @param int $auth_id
     * @param string $service_name
     * @return bool
     */
    public function subscribeAuthToService($auth_id, $service_name): bool
    {
        $auth = $this->db->query('SELECT * FROM user_auth_info WHERE id = ?', $auth_id)->fetch();
        if (!$auth)
            return false;

        $svcs = unserialize($auth['services']);
        $svcs[] = $service_name;

        $this->db->query('UPDATE user_auth_info SET services = ? WHERE id = ?', serialize($svcs), $auth_id);
        return true;
    }

    /**
     * Generates a new token using given auth info id (we take services and user id from there)
     * and valid time in seconds
     * @param int $auth_info_id
     * @param int $valid_for
     * @return TokenInfo
     */
    public function generateToken($auth_info_id, $valid_for = self::TOKEN_VALIDITY_DEFAULT): TokenInfo
    {
        if ($valid_for < self::TOKEN_VALIDITY_MIN || $valid_for > self::TOKEN_VALIDITY_MAX)
            return new TokenInfo();

        $rec = $this->db->query("SELECT * FROM user_auth_info WHERE id = ?", $auth_info_id)->fetch();
        if (!$rec)
            return new TokenInfo();

        // token is generated using cryptographically safe generator built into PHP, and then converted to a string
        $token = bin2hex(random_bytes(self::TOKEN_BYTE_LENGTH));

        $this->db->query("INSERT INTO user_auth_token", [
            'users_id' => $rec['users_id'],
            'services' => $rec['services'],
            'value' => $token,
            'valid_from' => new DateTime(),
            'valid_to' => new DateTime('+'.$valid_for.' seconds')
        ]);

        return new TokenInfo($this->db->getInsertId(), $rec['users_id'], unserialize($rec['services']), true, $token);
    }

    /**
     * Retrieves token info, if the token is valid
     * @param string $token
     * @return TokenInfo
     */
    public function getTokenInfo($token): TokenInfo
    {
        $rec = $this->db->query("SELECT * FROM user_auth_token WHERE value = ?", $token)->fetch();
        if (!$rec || !$this->isAuthDateValid($rec))
            return new TokenInfo(); // this will generate invalid token info

        return new TokenInfo($rec['id'], $rec['users_id'], unserialize($rec['services']), true, $rec['value']);
    }

    /**
     * Retrieves existing token for requested services, but only if it already exists!
     * @param int $users_id
     * @param array $services
     * @return TokenInfo
     */
    public function getTokenForServices($users_id, $services): TokenInfo
    {
        $userTokens = $this->db->query("SELECT * FROM user_auth_token WHERE users_id = ?", $users_id)->fetchAll();

        foreach ($userTokens as $rec)
        {
            if ($this->isAuthDateValid($rec) && count(array_intersect($services, unserialize($rec['services']) )) === count($services))
                return new TokenInfo($rec['id'], $rec['users_id'], unserialize($rec['services']), true, $rec['value']);
        }

        return new TokenInfo();
    }

    /**
     * Removes an existing token with given ID
     * @param int $id
     */
    public function removeToken($id)
    {
        $this->db->query("DELETE FROM user_auth_token WHERE id = ?", $id);
    }
}
