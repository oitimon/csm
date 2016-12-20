<?php

namespace Csm;

use Csm\Contracts\Serializable;
use Csm\Traits\Parameters;

/**
 * @author Oleksandr Ieremeev
 * @package Csm
 */
class CsmIdent implements Serializable, \Countable
{
    use Parameters;

    /**
     * @var array
     */
    protected $strIdents;

    /**
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = array_merge($this->getDefaultParams(), $params);
        $this->strIdents = [];
    }

    /**
     * @return static
     */
    public static function create(array $params = array())
    {
        return new static($params);
    }

    /**
     * @param string $ident
     * @return CsmIdent
     */
    public function addResourceName($ident)
    {
        return $this->addTypeIdent(static::IDENT_TYPE_DIR, (string) $ident);
    }

    /**
     * @param string $ident
     * @return CsmIdent
     */
    public function addString($ident)
    {
        return $this->addTypeIdent(static::IDENT_TYPE_STRING, (string) $ident);
    }

    /**
     * @param int $ident
     * @return CsmIdent
     */
    public function addNumeric($ident)
    {
        return $this->addTypeIdent(static::IDENT_TYPE_NUMERIC, (int) $ident);
    }

    /**
     * @param mixed $ident
     * @return CsmIdent
     */
    public function addHash($ident)
    {
        return $this->addTypeIdent(static::IDENT_TYPE_HASH_MD5, md5($ident));
    }

    /**
     * Check Ident created correct before use. Generate CsmException if not.
     * If strict param set as false just returns false instead of exception.
     *
     * Returns true if Ident is ok.
     *
     * @throws CsmException
     * @return bool
     */
    public function checkIdent()
    {
        $result = true;
        $strict = $this->params[static::PARAM_IS_STRICT];
        $count = $this->count();
        if ($this->params[static::PARAM_MIN_IDENTS] > $count) {
            $result = $this->generateError(
                $strict,
                sprintf('CSM Ident must have %s or more rows', $this->params[static::PARAM_MIN_IDENTS])
            );
        } elseif ($this->params[static::PARAM_MAX_IDENTS] < $count) {
            $result = $this->generateError(
                $strict,
                sprintf('CSM Ident must have not more than %s rows', $this->params[static::PARAM_MAX_IDENTS])
            );
        }
        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->strIdents;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return json_encode($this->strIdents);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->strIdents);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->toString();
    }

    /**
     * @param string $type
     * @param mixed $ident
     * @return CsmIdent
     */
    protected function addTypeIdent($type, $ident)
    {
        $this->strIdents[] = ['t' => (string) $type, 'i' => $ident];
        return $this;
    }

    const IDENT_TYPE_UNDEFINED = 'u';
    const IDENT_TYPE_NUMERIC   = 'n';
    const IDENT_TYPE_STRING    = 's';
    const IDENT_TYPE_HASH_MD5  = 'h';
    const IDENT_TYPE_DIR       = 'd';

    const PARAM_MIN_IDENTS     = 'min';
    const PARAM_MAX_IDENTS     = 'max';
    const PARAM_IS_STRICT      = 'strict';

    const PARAMS = [
        'min'    => 1,
        'max'    => 100,
        'strict' => true
    ];
}
