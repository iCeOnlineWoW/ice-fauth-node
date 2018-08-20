<?php

/**
 * Container for request parameters
 */
class ParameterContainer
{
    /** @var array */
    private $params;

    public function __construct(array $inputArray) {
        $this->params = $inputArray;
    }

    /**
     * Retrieve parameter, if not present, use default
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (isset($this->params[$key]))
            return $this->params[$key];
        return $default;
    }
}
