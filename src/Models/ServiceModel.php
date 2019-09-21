<?php

/**
 * Model for all service-related routines
 */
class ServiceModel extends BaseModel
{
    /** Maximum number of characters in a single encoded user service data */
    const MAX_USERDATA_ENCODED_LENGTH = 1024;

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
     * Validates service secret proof
     * @param string $name
     * @param string $secret
     * @return bool
     */
    public function validateServiceSecret($name, $secret): bool
    {
        $service = $this->getServiceByName($name);
        if (!$service)
            return false;
        if (strcmp($service['secret'], $secret) !== 0)
            return false;
        return true;
    }

    /**
     * Retrieves service stored data for given user
     * @param int $users_id
     * @param int $services_id
     * @return array
     */
    public function getUserServiceData($users_id, $services_id): array
    {
        $res = $this->db->query("SELECT data FROM user_service_data WHERE users_id = ? AND services_id = ?", $users_id, $services_id)->fetch();
        if (!$res || !$res['data'] || strlen($res['data']) === 0)
            return [];

        $decoded = json_decode($res['data'], true);
        if (!$decoded)
            return [];

        return $decoded;
    }

    /**
     * Stores user service data to database; may fail - the data is limited by maximum length when encoded
     * The caller is responsible for validating service existence
     * @return bool
     */
    public function setUserServiceData($users_id, $services_id, $data): bool
    {
        $encoded = json_encode($data);
        if (strlen($encoded) > self::MAX_USERDATA_ENCODED_LENGTH)
            return false;

        $this->db->query("UPDATE user_service_data SET data = ? WHERE users_id = ? AND services_id = ?", $encoded, $users_id, $services_id);

        return true;
    }

    /**
     * Retrieves all mediated services by given service
     * @param int $service_id
     * @return array
     */
    public function getMediatedServiceArray(int $service_id): array
    {
        $svcs = $this->db->query("SELECT mediated_service_id FROM service_mediator WHERE parent_service_id = ?", $service_id);

        if (!$svcs)
            return [];

        $res = [];
        foreach ($svcs as $svc)
            $res[] = $svc['mediated_service_id'];

        return $res;
    }

    /**
     * Determines, whether the service could be mediated by given parent service or not
     * @param int $parent_service_id
     * @param int $mediated_service_id
     * @return bool
     */
    public function isMediatedByService(int $parent_service_id, int $mediated_service_id): bool
    {
        $med = $this->db->query("SELECT mediated_service_id FROM service_mediator ".
                "WHERE parent_service_id = ? AND mediated_service_id = ?",
                $parent_service_id, $mediated_service_id)->fetch();

        if (!$med)
            return false;

        // Future versions may include some mediating conditions here (time-limited mediation? context-based mediation?)

        return true;
    }
}
