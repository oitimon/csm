<?php

namespace Csm\Driver;

use Csm\Contracts\Driver;
use Csm\Driver\Filesystem\System;
use Csm\Driver\Traits\Helper;
use Csm\Driver\Traits\Main;

/**
 * @author Oleksandr Ieremeev
 * @package Csm
 */
class Filesystem implements Driver
{
    use Main, Helper, System;

    /**
     * If "noFileCache" set as true clear filesystem cache.
     *
     * @return void
     */
    protected function clearFileCache()
    {
        if ($this->params[static::PARAM_NO_FILE_CACHE]) {
            clearstatcache(true);
        }
    }

    const PARAM_RESOURCE_PATH  = 'resourcePath';
    const PARAM_RESOURCE_URL   = 'resourceUrl';
    const PARAM_DIR_MODE       = 'dirMode';
    const PARAM_FILE_MODE      = 'fileMode';
    const PARAM_NO_FILE_CACHE  = 'noFileCache';
    const PARAM_IS_STRICT      = 'strict';
    const PARAM_CHAIN_SIZE     = 'chainSize';
    const PARAM_READ_EVENT     = 'readEvent';
    const PARAM_WRITE_EVENT    = 'writeEvent';

    const PARAMS = [
        'resourcePath' => null,
        'resourceUrl'  => null,
        'dirMode'      => 775,
        'fileMode'     => 775,
        'noFileCache'  => false,
        'strict'       => true,
        'chainSize'    => 65536,
        'readEvent'    => false,
        'writeEvent'   => false
    ];
}
