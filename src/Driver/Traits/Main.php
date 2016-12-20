<?php

namespace Csm\Driver\Traits;

use Csm\CsmException;
use Csm\CsmIdent;
use Csm\Traits\Parameters;

/**
 * @author Oleksandr Ieremeev
 * @package Csm
 */
trait Main
{
    use Parameters;

    /**
     * Get content by Ident and Name.
     * If content is absent generates CsmException in strict mode or returns false if is not strict.
     *
     * @param CsmIdent $ident
     * @param string $name
     * @throws CsmException
     * @return string | bool
     */
    public function get(CsmIdent $ident, $name)
    {
        $path = $this->prepareFullPath(
            $this->params[static::PARAM_RESOURCE_PATH],
            $this->getPathAsArray($ident),
            $this->params[static::PARAM_DIR_MODE]
        );

        $result = $this->readFile($path, $name);
        if ($result === false) {
            $this->generateError(
                $this->params[static::PARAM_IS_STRICT],
                sprintf('Can not read file %s', var_export(error_get_last(), true))
            );
        }

        return $result;
    }

    /**
     * Save content by Ident and Name.
     * If can not save generate CsmException in strict mode or returns false if is not strict.
     *
     * @param CsmIdent $ident
     * @param string $content
     * @param string $name
     * @throws CsmException
     * @return bool
     */
    public function set(CsmIdent $ident, $content, $name)
    {
        $path = $this->prepareFullPath(
            $this->params[static::PARAM_RESOURCE_PATH],
            $this->getPathAsArray($ident),
            $this->params[static::PARAM_DIR_MODE]
        );

        if (!$this->saveFile($path, $name, $content, $this->params[static::PARAM_FILE_MODE])) {
            $result = $this->generateError(
                $this->params[static::PARAM_IS_STRICT],
                sprintf('Can not save file %s', var_export(error_get_last(), true))
            );
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * Delete content by Ident (and Name).
     * If can not delete generates CsmException in strict mode or returns false if is not strict.
     *
     * @param CsmIdent $ident
     * @param string $name
     * @throws CsmException
     * @return bool
     */
    public function delete(CsmIdent $ident, $name = null)
    {
        $path = $this->prepareFullPath(
            $this->params[static::PARAM_RESOURCE_PATH],
            $this->getPathAsArray($ident),
            $this->params[static::PARAM_DIR_MODE]
        );

        if (!$this->deleteFile($path, $name)) {
            $result = $this->generateError(
                $this->params[static::PARAM_IS_STRICT],
                sprintf('Can not delete file %s', var_export(error_get_last(), true))
            );
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * Check content is present by Ident and Name.
     *
     * @param CsmIdent $ident
     * @param string $name
     * @throws CsmException
     * @return bool
     */
    public function isPresent(CsmIdent $ident, $name)
    {
        $path = $this->prepareFullPath(
            $this->params[static::PARAM_RESOURCE_PATH],
            $this->getPathAsArray($ident),
            $this->params[static::PARAM_DIR_MODE]
        );

        return $this->isFilePresent($path, $name);
    }

    /**
     * Get content URL by Ident and Name.
     * If content is absent returns false in strict mode or generated url if is not strict.
     *
     * @param CsmIdent $ident
     * @param string $name
     * @throws CsmException
     * @return string | bool
     */
    public function getPreparedUrl(CsmIdent $ident, $name)
    {
        $url = $this->params[static::PARAM_RESOURCE_URL];
        $dirPaths = $this->getPathAsArray($ident);
        $path = $this->prepareFullPath(
            $this->params[static::PARAM_RESOURCE_PATH],
            $dirPaths,
            $this->params[static::PARAM_DIR_MODE]
        );

        return (!$this->params[static::PARAM_IS_STRICT] || $this->isFilePresent($path, $name))
            ? $this->getFullPath($url, $dirPaths).'/'.$name
            : false;
    }

    /**
     * Copy content from source Ident to destination (with same name ot another).
     * If some error generates CsmException in strict mode or returns false if is not strict.
     *
     * @param CsmIdent $sourceIdent
     * @param CsmIdent $destIdent
     * @param string $name
     * @param string $destName
     * @throws CsmException
     * @return bool
     */
    public function copy(CsmIdent $sourceIdent, CsmIdent $destIdent, $name, $destName = '')
    {
        $sourcePath = $this->prepareFullPath(
            $this->params[static::PARAM_RESOURCE_PATH],
            $this->getPathAsArray($sourceIdent),
            $this->params[static::PARAM_DIR_MODE]
        );
        $destPath = $this->prepareFullPath(
            $this->params[static::PARAM_RESOURCE_PATH],
            $this->getPathAsArray($destIdent),
            $this->params[static::PARAM_DIR_MODE]
        );
        if (!$this->copyFile($sourcePath, $destPath, $name, $destName)) {
            $result = $this->generateError(
                $this->params[static::PARAM_IS_STRICT],
                sprintf('Can not copy file %s', var_export(error_get_last(), true))
            );
        } else {
            $result = true;
        }
        return $result;
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
    abstract protected function prepareFullPath($startPath, array $dirs, $mode = 777);

    /**
     * Returns array of all dirs for Ident.
     *
     * @param CsmIdent $ident
     * @return array
     */
    abstract protected function getPathAsArray(CsmIdent $ident);

    /**
     * Make path as string from path-array.
     *
     * @param string $startPath
     * @param array $dirs
     * @return string
     */
    abstract protected function getFullPath($startPath, array $dirs);

    /**
     * Read content from storage.
     * Returns false if can not read file.
     *
     * @param string $path
     * @param string $name
     * @return string | boolen
     */
    abstract protected function readFile($path, $name);

    /**
     * Save content to the storage.
     *
     * @param string $path
     * @param string $name
     * @param string $content
     * @param int $mode
     * @return bool
     */
    abstract protected function saveFile($path, $name, $content, $mode = 777);

    /**
     * Delete content from storage.
     * Returns false if error.
     *
     * @param string $path
     * @param string $name
     * @return bool
     */
    abstract protected function deleteFile($path, $name);

    /**
     * Check content in the storage.
     * Returns false if file is not present.
     *
     * @param string $path
     * @param string $name
     * @return bool
     */
    abstract protected function isFilePresent($path, $name);

    /**
     * Copy file by name from source path to destination in one storage.
     * Returns false if error.
     *
     * @param string $sourcePath
     * @param string $destPath
     * @param string $name
     * @param string $destName
     * @return bool
     */
    abstract protected function copyFile($sourcePath, $destPath, $name, $destName = '');
}
