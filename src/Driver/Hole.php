<?php

namespace Csm\Driver;

use Csm\Contracts\Driver;
use Csm\CsmException;
use Csm\CsmIdent;
use Csm\Traits\Parameters;

/**
 * @author Oleksandr Ieremeev
 * @package Csm
 */
class Hole implements Driver
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
        return $this->check($name, 'Can not read file') === true ?
            'content' : false;
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
        return $this->check($name, 'Can not save file');
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
        return $this->check($name, 'Can not delete file');
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
        return $name == 'error' ? false : true;
    }

    /**
     * Get content URL by Ident and Name.
     * Returns false if content does not present.
     *
     * @param CsmIdent $ident
     * @param string $name
     * @throws CsmException
     * @return string | bool
     */
    public function getPreparedUrl(CsmIdent $ident, $name)
    {
        return $name == 'error' ? false : 'http://url';
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
        return $this->check($name, 'Can not copy file');
    }

    /**
     * @param string $name
     * @param string $message
     * @return bool
     */
    protected function check($name, $message)
    {
        if ($name == 'error') {
            $result = $this->generateError(
                $this->params[static::PARAM_IS_STRICT],
                $message
            );
        } else {
            $result = true;
        }

        return $result;
    }

    const PARAM_IS_STRICT = 'strict';

    const PARAMS = [
        'strict' => true
    ];
}
