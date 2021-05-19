<?php

namespace Bermuda\Authentication;

/**
 * Interface SessionRepositoryInterface
 * @package Bermuda\Authentication
 */
interface SessionStorageInterface
{
    /**
     * @param string $sid
     * @return SessionInterface|null
     */
    public function get(string $sid):? SessionInterface ;
    
    /**
     * @param UserInterface $user
     * @param ServerRequestInterface $request
     * @return SessionInterface
     */
    public function make(UserInterface $user, array $payload): SessionInterface ;

    /**
     * @param SessionInterface $session
     */
    public function store(SessionInterface $session): void ;

    /**
     * 
     * @param string[] $sid array of sessions id
     */
    public function remove(array $sid): void ;
    
    /**
     * @param string $userId
     */
    public function removeAllUserSessions(string $userId): void ;
    
    /**
     * Remove all expired sessions
     */
    public function removeExpired(): void ;
}
