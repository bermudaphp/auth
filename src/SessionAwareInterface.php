<?php


namespace Bermuda\Authentication;


/**
 * Interface SessionAwareInterface
 * @package Bermuda\Authentication
 */
interface SessionAwareInterface
{
    /**
     * @return Sessions
     */
    public function sessions(): Sessions ;
}
