<?php

namespace Bermuda\Authentication;

use Bermuda\String\Stringable;

/**
 * Interface UserInterface
 * @package Bermuda\Authentication
 */
interface UserInterface
{
    /**
     * @return string|null
     */
    public function getId():? string ;

    /**
     * @return string
     */
    public function getCredential(): string ;
}
