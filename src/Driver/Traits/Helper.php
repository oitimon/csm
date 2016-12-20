<?php

namespace Csm\Driver\Traits;

use Csm\CsmException;
use Csm\CsmIdent;

/**
 * @author Oleksandr Ieremeev
 * @package Csm
 */
trait Helper
{
    /**
     * Returns array of all dirs for Ident.
     *
     * @param CsmIdent $ident
     * @return array
     */
    protected function getPathAsArray(CsmIdent $ident)
    {
        $dirs = [];

        foreach ($ident->toArray() as $row) {
            if (in_array(
                $row['t'],
                [CsmIdent::IDENT_TYPE_STRING, CsmIdent::IDENT_TYPE_NUMERIC, CsmIdent::IDENT_TYPE_HASH_MD5]
            )) {
                $method = 'prepareElement'.ucfirst($row['t']);
            } else {
                $method = 'prepareElementDir';
            }
            $dirs = array_merge($dirs, $this->$method($row['i']));
        }

        return $dirs;
    }

    /**
     * Prepare any ident as dir-ident.
     *
     * @param string $id
     * @return string[]
     */
    protected function prepareElementDir($id)
    {
        return array($this->correctName($id));
    }

    /**
     * Prepare any string-ident as dir-ident.
     *
     * @param string $id
     * @return array
     */
    protected function prepareElementS($id)
    {
        $id = (string) $id;
        $dirs = [];
        $length = strlen($id);
        for ($i = 0; $i < $length; $i++) {
            $dirs[] = $this->correctName(ord($id[$i]));
        }

        return $dirs;
    }

    /**
     * Prepare any numeric-ident as dir-ident.
     *
     * @param string $id
     * @return array
     */
    protected function prepareElementN($id)
    {
        $id = (string) $id;
        $dirs = array();
        $length = strlen($id);
        for ($i = 0; $i < $length; $i += 2) {
            $str = $id[$i];
            if ($i < $length - 1) {
                $str .= $id[$i + 1];
            }
            $dirs[] = $this->correctName($str);
        }

        return $dirs;
    }

    /**
     * Prepare any ident as dir-ident.
     *
     * @param string $id
     * @return array
     */
    protected function prepareElementH($id)
    {
        $id = (string) $id;
        $dirs = array();
        $length = strlen($id);
        for ($i = 0; $i < $length; $i += 4) {
            $str = substr($id, $i, 4);
            $dirs[] = $this->correctName($str);
        }

        return $dirs;
    }

    /**
     * Prepare name as name of file.
     *
     * @param string $id
     * @return string
     */
    protected function correctName($id)
    {
        return preg_replace('([^\w])', '_', $id);
    }

    /**
     * Try to prepare full path and return it (autocreating directories).
     *
     * @param string $startPath
     * @param array $dirs
     * @param int $mode
     * @throws CsmException
     * @return string
     */
    protected function prepareFullPath($startPath, array $dirs, $mode = 777)
    {
        $mode = octdec($mode);
        $this->clearFileCache();

        // check full path before, maybe it is ready
        $readyPath = $this->preCheckFullPath($startPath, $dirs);

        if ($readyPath === false) {
            foreach ($dirs as $dir) {
                $startPath .= '/'.$dir;
                $this->prepareOneElementDirectory($startPath, $mode);
            }
        } else {
            $startPath = $readyPath;
        }

        return $startPath;
    }

    /**
     * Make path as string from path-array.
     *
     * @param string $startPath
     * @param array $dirs
     * @return string
     */
    protected function getFullPath($startPath, array $dirs)
    {
        return $startPath.'/'.implode('/', $dirs);
    }

    /**
     * If "noFileCache" set as true clear storage cache.
     *
     * @return void
     */
    abstract protected function clearFileCache();

    /**
     * @param string $path
     * @param array $dirs
     * @return string | bool
     * @throws CsmException
     */
    abstract protected function preCheckFullPath($path, array $dirs);

    /**
     * @param string $path
     * @param int $mode
     * @throws CsmException
     */
    abstract protected function prepareOneElementDirectory($path, $mode);
}
