<?php

namespace Bermuda\Authentication;

/**
 * Interface SessionInterface
 * @package Bermuda\Authentication
 */
interface SessionInterface
{
    /**
     * @param string|null $id
     * @return string
     */
    public function id(?string $id = null): string;
    
    /**
     * @return UserInterface
     */
    public function getUser(): UserInterface;
 
    /**
     * @param array|null $payload
     * @return array|null
     */
    public function payload(?array $payload = null):? array ;

    /**
     * @param \DateTimeInterface|null $activity
     * @return \DateTimeInterface
     */
    public function activity(\DateTimeInterface $activity = null): \DateTimeInterface ;
}
