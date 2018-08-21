<?php

/**
 * Model for all service-related routines
 */
class ServiceModel extends BaseModel
{
    public function __construct($dbInstance)
    {
        parent::__construct($dbInstance);
    }

    /**
     * Retrieves service by its ID
     * @param int $id
     * @return array | null
     */
    public function getServiceById($id)
    {
        return $this->db->query("SELECT * FROM services WHERE id = ?", $id)->fetch();
    }

    /**
     * Retrieves service by its name
     * @param string $name
     * @return array | null
     */
    public function getServiceByName($name)
    {
        return $this->db->query("SELECT * FROM services WHERE name = ?", $name)->fetch();
    }

    /**
     * Retrieves service stored data for given user
     * @param int $users_id
     * @param int $services_id
     * @return array
     */
    public function getUserServiceData($users_id, $services_id): array
    {
        $res = $this->db->query("SELECT * FROM user_service_data WHERE users_id = ? AND services_id = ?", $users_id, $services_id)->fetch();
        if (!$res || !$res['data'] || strlen($res['data']) === 0)
            return [];

        $decoded = json_decode($res['data'], true);
        if (!$decoded)
            return [];

        return $decoded;
    }
}
