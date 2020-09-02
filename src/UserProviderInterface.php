<?php


namespace Bermuda\Authentication;


/**
 * Interface UserProviderInterface
 * @package Bermuda\Authentication
 */
interface UserProviderInterface
{
    /**
     * @param $identity
     * @return UserInterface|null
     */
    public function provide($identity):? UserInterface ;
}
