<?php

namespace Csm;

use Csm\Contracts\Serializable;

/**
 * @author Oleksandr Ieremeev
 * @package Csm
 */
class CsmType implements Serializable
{
    /**
     * @var string
     */
    protected $type;
    /**
     * @var string
     */
    protected $subType;

    /**
     * @param string $type
     * @param string $subType
     */
    public function __construct($type = 'any', $subType = null)
    {
        $this->setType($type, $subType);
    }

    /**
     * @param string $type
     * @param string $subType
     * @return  CsmType
     */
    public function setType($type, $subType = null)
    {
        $this->type = (string) $type;
        if ($subType != '') {
            $this->subType = (string) $subType;
        }
        return $this;
    }

    /**
     * @param string $subType
     * @return CsmType
     */
    public function setSubType($subType)
    {
        $this->subType = (string) $subType;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getSubType()
    {
        return $this->subType;
    }

    /**
     * @param string $type
     * @param string $subType
     * @return CsmType
     */
    public static function create($type = 'any', $subType = null)
    {
        return new static($type, $subType);
    }

    /**
     * @param string | array $value
     * @return CsmType
     */
    public static function createHydrate($value)
    {
        return (new static)->hydrate($value);
    }

    /**
     * @param string | array $value
     * @return CsmType
     */
    public function hydrate($value)
    {
        return is_array($value) ? $this->hydrateArray($value) : $this->hydrateString($value);
    }

    /**
     * @param string $types
     * @return CsmType
     */
    protected function hydrateString($types)
    {
        $types = explode('_', $types);
        return $this->hydrateArray(is_array($types) ? $types : []);
    }

    /**
     * @param array $types
     * @return CsmType
     */
    protected function hydrateArray(array $types)
    {
        return $this->setType(
            isset($types[0]) && $types[0] != '' ? $types[0] : static::TYPE_ANY,
            isset($types[1]) ? $types[1] : null
        );
    }

    /**
     * @return string
     */
    public function toString()
    {
        $result = $this->type;
        if ($this->subType != '') {
            $result .= '_'.(string) $this->subType;
        }
        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [$this->type, $this->subType];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    const TYPE_ANY          = 'any';
    const TYPE_IMAGE        = 'img';
    const TYPE_VIDEO        = 'video';
    const TYPE_XML          = 'xml';
    const TYPE_PDF          = 'pdf';
    const TYPE_CSV          = 'csv';
    const TYPE_TEXT         = 'txt';

    const TYPES = [
        'any', 'img', 'video', 'xml', 'pdf', 'csv', 'txt'
    ];
}
