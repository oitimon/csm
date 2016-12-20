<?php

namespace Csm\Traits;

use Csm\CsmException;

/**
 * @author Oleksandr Ieremeev
 * @package Csm
 */
trait Parameters
{
    /**
     * @var array
     */
    protected $params;

    /**
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = array_merge($this->getDefaultParams(), $params);
    }

    /**
     * @return array
     */
    public function getDefaultParams()
    {
        return static::PARAMS;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $paramName
     * @return mixed
     */
    public function getParam($paramName)
    {
        return isset($this->params[$paramName]) ? $this->params[$paramName] : null;
    }

    /**
     * @param string $paramName
     * @param mixed $value
     * @return void
     */
    public function setParam($paramName, $value)
    {
        $this->params[$paramName] = $value;
    }

    /**
     * @param bool $strict
     * @param string $message
     * @return bool
     * @throws CsmException
     */
    protected function generateError($strict, $message)
    {
        if ($strict) {
            throw new CsmException($message);
        }
        return false;
    }
}
