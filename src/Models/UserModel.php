<?php

/**
 * Model for all user-related routines
 * Do NOT confuse with authentication routines, they belong to AuthModel
 */
class UserModel extends BaseModel
{
    public function __construct($dbInstance)
    {
        parent::__construct($dbInstance);
    }

    /**
     * Retrieves user by his ID
     * @param int $id
     * @return array | null
     */
    public function getUserById($id)
    {
        return $this->db->query("SELECT * FROM users WHERE id = ?", $id)->fetch();
    }

    /**
     * Retrieves user by his username
     * @param string $username
     * @return array | null
     */
    public function getUserByUsername($username)
    {
        return $this->db->query("SELECT * FROM users WHERE username = ?", $username)->fetch();
    }

    /**
     * Retrieves user by his email
     * @param string $email
     * @return array | null
     */
    public function getUserByEmail($email)
    {
        return $this->db->query("SELECT * FROM users WHERE email = ?", $email)->fetch();
    }

    /**
     * Creates a new user and returns his database ID
     * @param string $username
     * @param string $email
     * @return int
     */
    public function addUser($username, $email): int
    {
        $this->db->query("INSERT INTO users", [
            'username' => $username,
            'email' => $email
        ]);

        return $this->db->getInsertId();
    }
}
