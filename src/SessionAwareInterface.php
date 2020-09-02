<?php


namespace App\Authentication;


/**
 * Interface SessionAwareInterface
 * @package App\Auth
 */
interface SessionAwareInterface
{
    /**
     * @return Sessions
     */
    public function sessions(): Sessions ;
}