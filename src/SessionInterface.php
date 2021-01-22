<?php

namespace Bermuda\Authentication;

/**
 * Interface SessionInterface
 * @package Bermuda\Authentication
 */
interface SessionInterface
{
    /**
     * @return string|int
     */
    public function getId();
    
    /**
     * @return UserInterface
     */
    public function getUser(): UserInterface;

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name);

    /**
     * @param string $name
     * @param $value
     * @return mixed
     */
    public function __set(string $name, $value);
    
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
