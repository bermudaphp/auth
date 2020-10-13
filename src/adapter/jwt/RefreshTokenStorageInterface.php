<?php


namespace Bermuda\Authentication\Adapter\Jwt;


/**
 * Interface RefreshTokenStorageInterface
 * @package Bermuda\Authentication\Adapter\Jwt
 */
interface RefreshTokenStorageInterface
{
    /**
     * @param string $refreshToken
     * @return bool
     */
    public function hasToken(string $refreshToken): bool ;

    /**
     * @param string $refreshToken
     */
    public function storeToken(string $refreshToken): void ;

    /**
     * @param string $refreshToken
     * @return string
     */
    public function getUserIdentityFromRefreshToken(string $refreshToken): string ;
}
