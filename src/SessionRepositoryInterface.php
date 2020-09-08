<?php


namespace Bermuda\Authentication;


/**
 * Interface SessionRepositoryInterface
 * @package Bermuda\Authentication
 */
interface SessionRepositoryInterface
{
    /**
     * @param string|int $id
     * @return SessionInterface|null
     */
    public function get($id):? SessionInterface ;
    
    /**
     * @param UserInterface $user
     * @param ServerRequestInterface $request
     * @return SessionInterface
     */
    public function make(UserInterface $user, ServerRequestInterface $request): SessionInterface ;

    /**
     * @param SessionInterface $session
     */
    public function store(SessionInterface $session): void ;

    /**
     * @param SessionInterface $session
     */
    public function remove(SessionInterface $session): void ;
    
    /**
     * @param array $ids
     */
    public function removeByIds(array $ids): void ;
    
    /**
     * Remove all expired sessions
     */
    public function removeExpired(): void ;
}
