<?php

namespace Csm\Driver\Filesystem;

use Csm\Containers\ChainElement;
use Csm\CsmException;

/**
 * @author Oleksandr Ieremeev
 * @package Csm
 */
trait System
{
    /**
     * @param string $path
     * @param int $mode
     * @throws CsmException
     */
    protected function prepareOneElementDirectory($path, $mode)
    {
        if (!@is_dir($path)) {
            if (!@mkdir($path) || !@chmod($path, $mode)) {
                throw new CsmException('Can not create directory '.$path);
            }
        }
    }

    /**
     * @param string $path
     * @param array $dirs
     * @return string | bool
     * @throws CsmException
     */
    protected function preCheckFullPath($path, array $dirs)
    {
        $fullPath = $this->getFullPath($path, $dirs);
        $result = @is_dir($fullPath) ? $fullPath : false;
        if ($result === false && !@is_dir($path)) {
            throw new CsmException($path.' is not a directory');
        }
        return $result;
    }

    /**
     * Save content directly to os's filesystem.
     *
     * @param string $path
     * @param string $name
     * @param string $content
     * @param int $mode
     * @return bool
     */
    protected function saveFile($path, $name, $content, $mode = 777)
    {
        $mode = octdec($mode);
        $fullFileName = $path.'/'.$name;
        $fp = @fopen($fullFileName, 'w');
        $chainElement = ChainElement::start(strlen($content));
        if ($fp !== false && $this->callWriteEvent($chainElement)) {
            $result = $this->tryWriteResource($fp, $content, $chainElement);
        } else {
            $result = false;
        }
        if (!$result && @is_file($fullFileName)) {
            $result &= @unlink($fullFileName);
        }
        return $result !== false && @chmod($fullFileName, $mode);
    }

    /**
     * Read content from filesystem.
     * Returns false if can not read file.
     *
     * @param string $path
     * @param string $name
     * @return string | boolen
     */
    protected function readFile($path, $name)
    {
        $fullFileName = $path.'/'.$name;
        $content = false;
        $fp = @fopen($fullFileName, 'r');
        $size = @filesize($fullFileName);
        if ($fp !== false && $size > 0) {
            $chainElement = ChainElement::start($size);
            if ($this->callReadEvent($chainElement)) {
                $content = $this->tryReadResource($fp, $chainElement);
            }
        }
        return $content;
    }

    /**
     * @param ChainElement $chainElement
     * @return bool
     */
    protected function callReadEvent(ChainElement $chainElement)
    {
        return $this->getParam(static::PARAM_READ_EVENT) ?
            (bool)call_user_func($this->getParam(static::PARAM_READ_EVENT), $chainElement) : true;
    }

    /**
     * @param ChainElement $chainElement
     * @return bool
     */
    protected function callWriteEvent(ChainElement $chainElement)
    {
        return $this->getParam(static::PARAM_WRITE_EVENT) ?
            (bool)call_user_func($this->getParam(static::PARAM_WRITE_EVENT), $chainElement) : true;
    }

    /**
     * Copy file by name from source path to destination.
     * Returns false if error.
     *
     * @param string $sourcePath
     * @param string $destPath
     * @param string $name
     * @param string $destName
     * @return bool
     */
    protected function copyFile($sourcePath, $destPath, $name, $destName = '')
    {
        if ($destName == '') {
            $destName = $name;
        }
        return @copy($sourcePath.'/'.$name, $destPath.'/'.$destName);
    }

    /**
     * Check content in the filesystem.
     * Returns false if file is not present.
     *
     * @param string $path
     * @param string $name
     * @return bool
     */
    protected function isFilePresent($path, $name)
    {
        return @is_file($path.'/'.$name);
    }

    /**
     * Delete content from filesystem.
     * Returns false if error.
     *
     * @param string $path
     * @param string $name
     * @return bool
     */
    protected function deleteFile($path, $name)
    {
        return @unlink($path.'/'.$name);
    }

    /**
     * @param resource $fp
     * @return string | bool
     */
    protected function tryReadResource($fp, ChainElement $chainElement)
    {
        $content = false;
        $chainSize = $this->getParam(static::PARAM_CHAIN_SIZE);
        while (!feof($fp)) {
            $content = (string)$content.@fread($fp, $chainSize);
            if (!$this->callReadEvent($chainElement->update(strlen($content)))) {
                $content = false;
                break;
            }
        }
        return @fclose($fp) ? $content : false;
    }

    /**
     * @param resource $fp
     * @param string $content
     * @return bool
     */
    protected function tryWriteResource($fp, $content, ChainElement $chainElement)
    {
        $chainSize = $this->getParam(static::PARAM_CHAIN_SIZE);
        $pieces = str_split($content, $chainSize);
        $saved = 0;
        $result = true;
        foreach ($pieces as $piece) {
            $saved += @fwrite($fp, $piece, strlen($piece));
            $result = $this->callWriteEvent($chainElement->update($saved));
            if (!$result) {
                break;
            }
        }
        return @fclose($fp) && $result;
    }

    /**
     * Make path as string from path-array.
     *
     * @param string $startPath
     * @param array $dirs
     * @return string
     */
    abstract protected function getFullPath($startPath, array $dirs);

    /**
     * @param string $paramName
     * @return mixed
     */
    abstract public function getParam($paramName);
}
