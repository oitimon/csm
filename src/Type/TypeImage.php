<?php

namespace Csm\Type;

use Csm\CsmType;

/**
 * @author Oleksandr Ieremeev
 * @package Csm
 */
class TypeImage extends CsmType
{

    /**
     * @param string $type
     * @param string $subType
     * @return TypeImage
     */
    public static function create($type = 'img', $subType = null)
    {
        return new static($type, $subType);
    }

    /**
     * @param string $subType
     * @return TypeImage
     */
    public static function createImage($subType = null)
    {
        return self::create(static::TYPE_IMAGE, $subType);
    }

    const SUBTYPE_JPEG      = 'jpg';
    const SUBTYPE_GIF       = 'gif';
    const SUBTYPE_PNG       = 'png';
    const SUBTYPE_TIFF      = 'tif';
    const SUBTYPE_BMP       = 'bmp';

    const SUBTYPES = [
        'jpg', 'gif', 'png', 'tif', 'bmp'
    ];
}
