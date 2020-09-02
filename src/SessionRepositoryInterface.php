<?php


namespace App\Authentication;


/**
 * Interface SessionRepositoryInterface
 * @package App\Auth
 */
interface SessionRepositoryInterface
{
    /**
     * @param string|int $id
     * @return SessionInterface
     */
    public function get($id): SessionInterface ;

    /**
     * @param SessionInterface $session
     */
    public function store(SessionInterface $session): void ;

    /**
     * @param SessionInterface $session
     */
    public function remove(SessionInterface $session): void ;
}