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
     * @return Stringable|int|string|null
     */
    public function getId();

    /**
     * @return string|Stringable
     */
    public function getCredential();
}
