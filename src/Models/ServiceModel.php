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
}
