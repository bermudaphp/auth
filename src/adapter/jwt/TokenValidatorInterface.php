<?php


namespace Bermuda\Authentication\Adapter\Jwt;


/**
 * Interface TokenValidatorInterface
 * @package App
 */
interface TokenValidatorInterface
{
    /**
     * @param string $token
     * @param array|null $parsedData
     * @return bool
     */
    public function validate(string $token, ?array & $parsedData = null): bool ;
}
