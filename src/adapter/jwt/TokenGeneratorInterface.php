<?php


namespace Bermuda\Authentication\Adapter\Jwt;


/**
 * Interface TokenGeneratorInterface
 * @package Bermuda\Authentication\Adapter\Jwt
 */
interface TokenGeneratorInterface
{
    /**
     * @param array $data
     * @return string
     */
    public function generateAccessToken(array $data = []): string ;

    /**
     * @param string $accessToken
     * @return string
     */
    public function generateRefreshToken(string $accessToken): string ;
}
