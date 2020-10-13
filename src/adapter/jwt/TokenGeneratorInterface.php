<?php


namespace App;


/**
 * Interface TokenGeneratorInterface
 * @package App
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