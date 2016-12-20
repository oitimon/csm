<?php

namespace Csm\Contracts;

/**
 * @author Oleksandr Ieremeev
 * @package Csm
 */
interface Serializable
{
    /**
     * Serialize object to array.
     *
     * @return array
     */
    public function toArray();

    /**
     * Serialize object to string.
     *
     * @return string
     */
    public function toString();
}
