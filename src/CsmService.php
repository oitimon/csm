<?php

namespace Csm;

use Csm\Contracts\Driver;
use Csm\Traits\Parameters;

/**
 * @author Oleksandr Ieremeev
 * @package Csm
 */
class CsmService
{
    /**
     * @var Driver[]
     */
    protected $drivers;
    /**
     * @var Driver[]
     */
    protected $customDrivers;

    use Parameters;

    /**
     * @param array $params
     * @throws CsmException
     */
    public function __construct(array $params)
    {
        $this->params = array_merge($this->getDefaultParams(), $params);
        $this->drivers = [];
        $this->customDrivers = [];
        if ($this->params[static::PARAM_DEFAULT_DRIVER] == '') {
            throw new CsmException('defaultDriver parameter is not set for CsmService');
        }
    }

    /**
     * Get Driver.
     * $driverName is null: default driver (parameter "defaultDriver");
     * $driverName is Driver: registers new driver and returns it;
     * $driverName is string: driver by name from parameters;
     * $drivername is array: generates, saves and returns driver by given parameters,
     *                       paramemeters must consist "type" as driver class name.
     *
     * @param mixed $driverName
     * @return Driver
     */
    public function getDriver($driverName = null)
    {
        if ($driverName instanceof Driver) {
            $driver = $this->registerDriverByDriver($driverName);
        } elseif (is_array($driverName)) {
            $driver = $this->getDriverByParams($driverName);
        } else {
            $driver = $this->getDriverByName($driverName);
        }
        return $driver;
    }

    /**
     * Get content by Ident and Name.
     * Optional: You can use ready driver or drivername (must be present in params)
     * If content is absent generates CsmException in strict mode or returns false if is not strict.
     *
     * @param CsmIdent $ident
     * @param string $name
     * @param mixed $driverName
     * @throws CsmException
     * @return string | bool
     */
    public function get(CsmIdent $ident, $name, $driverName = null)
    {
        return $this->getDriver($driverName)->get($ident, $name);
    }

    /**
     * Save content by Ident and Name.
     * If can not save generate CsmException in strict mode or returns false if is not strict.
     *
     * @param CsmIdent $ident
     * @param string $content
     * @param string $name
     * @param string $driverName
     * @throws CsmException
     * @return bool
     */
    public function set(CsmIdent $ident, $content, $name, $driverName = null)
    {
        return $this->getDriver($driverName)->set($ident, $content, $name);
    }

    /**
     * Delete content by Ident (and Name).
     * If can not delete generates CsmException in strict mode or returns false if is not strict.
     *
     * @param CsmIdent $ident
     * @param string $name
     * @param string $driverName
     * @throws CsmException
     * @return bool
     */
    public function delete(CsmIdent $ident, $name = null, $driverName = null)
    {
        return $this->getDriver($driverName)->delete($ident, $name);
    }

    /**
     * Check content is present by Ident and Name.
     *
     * @param CsmIdent $ident
     * @param string $name
     * @param string $driverName
     * @throws CsmException
     * @return bool
     */
    public function isPresent(CsmIdent $ident, $name, $driverName = null)
    {
        return $this->getDriver($driverName)->isPresent($ident, $name);
    }

    /**
     * Get content URL by Ident and Name.
     * If content is absent returns false in strict mode or generated url if is not strict.
     *
     * @param CsmIdent $ident
     * @param string $name
     * @param string $driverName
     * @throws CsmException
     * @return string | bool
     */
    public function getPreparedUrl(CsmIdent $ident, $name, $driverName = null)
    {
        return $this->getDriver($driverName)->getPreparedUrl($ident, $name);
    }

    /**
     * Copy content from source Ident to destination (with same name ot another).
     * Returns false if content does not present.
     *
     * @param CsmIdent $sourceIdent
     * @param CsmIdent $destIdent
     * @param string $name
     * @param string $destName
     * @param string $driverName
     * @throws CsmException
     * @return bool
     */
    public function copy(CsmIdent $sourceIdent, CsmIdent $destIdent, $name, $destName = '', $driverName = null)
    {
        return $this->getDriver($driverName)->copy($sourceIdent, $destIdent, $name, $destName);
    }

    /**
     * @param string $driverName
     * @throws CsmException
     * @return Driver
     */
    protected function getDriverByName($driverName = null)
    {
        if ($driverName == '') {
            $driver = $this->getDriverByName($this->params[static::PARAM_DEFAULT_DRIVER]);
        } else {
            $driver = isset($this->drivers[$driverName]) ? $this->drivers[$driverName] :
                $this->registerDriverByName($driverName);
        }
        return $driver;
    }

    /**
     * @param array $params
     * @throws CsmException
     * @return Driver
     */
    protected function getDriverByParams(array $params)
    {
        $hash = static::generateArrayHash($params);
        if (!isset($this->drivers[$hash])) {
            $this->registerDriverByParams($params, $hash);
        }
        return $this->drivers[$hash];
    }

    /**
     * @param array $array
     * @return string
     */
    protected static function generateArrayHash(array $array)
    {
        return md5(serialize($array));
    }

    /**
     * @param string $driverName
     * @throws CsmException
     * @return Driver
     */
    protected function registerDriverByName($driverName)
    {
        if (!isset($this->params[static::PARAM_DRIVERS][$driverName])) {
            throw new CsmException(
                sprintf('Drivername "%s" is not present in CsmService params', $driverName)
            );
        }
        if (!isset($this->params[static::PARAM_DRIVERS][$driverName][static::PARAM_TYPE])) {
            throw new CsmException(
                sprintf('Drivername "%s" has no driver type in CsmService params', $driverName)
            );
        }
        $classname = $this->params[static::PARAM_DRIVERS][$driverName][static::PARAM_TYPE];
        $this->drivers[$driverName] = new $classname(array_merge(
            $this->params[static::PARAM_DRIVERS][$driverName],
            $this->params[static::PARAM_DRIVERS]['all']
        ));
        return $this->drivers[$driverName];
    }

    /**
     * @param array $params
     * @param string $hash
     * @throws CsmException
     * @return Driver
     */
    protected function registerDriverByParams(array $params, $hash)
    {
        if (!isset($params[static::PARAM_TYPE])) {
            throw new CsmException('Custom driver has no driver type in params');
        }
        $classname = $params[static::PARAM_TYPE];
        $this->drivers[$hash] = new $classname(array_merge(
            $params,
            $this->params[static::PARAM_DRIVERS]['all']
        ));
        return $this->drivers[$hash];
    }

    /**
     * @param Driver $driver
     * @return Driver
     */
    protected function registerDriverByDriver(Driver $driver)
    {
        $this->drivers[static::generateArrayHash($driver->getParams())] = $driver;
        return $driver;
    }

    const PARAM_DEFAULT_DRIVER = 'defaultDriver';
    const PARAM_IDENT          = 'ident';
    const PARAM_DRIVERS        = 'drivers';
    const PARAM_TYPE           = 'type';

    const PARAMS = [
        'defaultDriver' => 'main',
        'ident' => [
        ],
        'drivers' => [
            'main' => [
                'type' => \Csm\Driver\Filesystem::class
            ],
            'all' => [
            ]
        ]
    ];
}
