<?php


namespace App\Authentication;


/**
 * Interface SessionInterface
 * @package App\Auth
 */
interface SessionInterface
{
    /**
     * @return string|int
     */
    public function getId();

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
     * @param \DateTimeInterface|null $activity
     * @return \DateTimeInterface
     */
    public function activity(\DateTimeInterface $activity = null): \DateTimeInterface ;
}