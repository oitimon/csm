<?php

namespace Csm\Contracts;

use Csm\CsmException;
use Csm\CsmIdent;

/**
 * @author Oleksandr Ieremeev
 * @package Csm
 */
interface Driver
{
    /**
     * Get content by Ident and Name.
     * If content is absent generates CsmException in strict mode or returns false if is not strict.
     *
     * @param CsmIdent $ident
     * @param string $name
     * @throws CsmException
     * @return string | bool
     */
    public function get(CsmIdent $ident, $name);

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
    public function set(CsmIdent $ident, $content, $name);

    /**
     * Delete content by Ident (and Name).
     * If can not delete generates CsmException in strict mode or returns false if is not strict.
     *
     * @param CsmIdent $ident
     * @param string $name
     * @throws CsmException
     * @return bool
     */
    public function delete(CsmIdent $ident, $name = null);

    /**
     * Check content is present by Ident and Name.
     * If content is absent returns false in strict mode or generated url if is not strict.
     *
     * @param CsmIdent $ident
     * @param string $name
     * @throws CsmException
     * @return bool
     */
    public function isPresent(CsmIdent $ident, $name);

    /**
     * Get content URL by Ident and Name.
     * If content is absent generates CsmException in strict mode or returns false if is not strict.
     *
     * @param CsmIdent $ident
     * @param string $name
     * @throws CsmException
     * @return string | bool
     */
    public function getPreparedUrl(CsmIdent $ident, $name);

    /**
     * Copy content from source Ident to destination (with same name ot another).
     * Returns false if content does not present.
     *
     * @param CsmIdent $sourceIdent
     * @param CsmIdent $destIdent
     * @param string $name
     * @param string $destName
     * @throws CsmException
     * @return bool
     */
    public function copy(CsmIdent $sourceIdent, CsmIdent $destIdent, $name, $destName = '');

    /**
     * Returns all parameters of this Driver.
     *
     * @return array
     */
    public function getParams();

    /**
     * @param string $paramName
     * @return mixed
     */
    public function getParam($paramName);

    /**
     * @param string $paramName
     * @param mixed $value
     * @return void
     */
    public function setParam($paramName, $value);
}
