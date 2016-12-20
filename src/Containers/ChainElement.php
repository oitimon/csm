<?php

namespace Csm\Containers;

use Csm\Contracts\Serializable;

/**
 * @author Oleksandr Ieremeev
 * @package Csm
 */
class ChainElement implements Serializable
{
    /**
     * Size of content in bytes.
     * @var int
     */
    public $size;
    /**
     * Size of proceed content in bytes.
     * @var int
     */
    public $bytesProceed;
    /**
     * Completed percentage (from 0 to 1).
     * @var float
     */
    public $completed;

    /**
     * @param int $size
     * @return ChainElement
     */
    public static function start($size)
    {
        $object = new static();
        $object->size = (int)$size;
        $object->bytesProceed = 0;
        $object->completed = 0;
        return $object;
    }

    /**
     * @param int $bytesProceed
     * @return ChainElement
     */
    public function update($bytesProceed)
    {
        $this->bytesProceed = (int)$bytesProceed;
        if ($this->size > 0) {
            $this->completed = $this->bytesProceed / $this->size;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'size' => $this->size,
            'bytesProceed' => $this->bytesProceed,
            'completed' => $this->completed
        ];
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->size.'_'.$this->bytesProceed.'_'.$this->completed;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
