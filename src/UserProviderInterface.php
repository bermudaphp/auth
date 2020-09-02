<?php


namespace App\Authentication;


/**
 * Interface UserProviderInterface
 * @package App\Auth
 */
interface UserProviderInterface
{
    /**
     * @param $identity
     * @return UserInterface|null
     */
    public function provide($identity):? UserInterface ;
}