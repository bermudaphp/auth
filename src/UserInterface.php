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
     * @param string $credential
     * @return bool
     */
    public function checkCredential(string $credential): bool ;
}
