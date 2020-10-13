<?php


namespace Bermuda\Authentication\Adapter\Jwt;


/**
 * Interface TokenParserInterface
 * @package Bermuda\Authentication\Adapter\Jwt
 */
interface TokenParserInterface
{
    /**
     * @param string $token
     * @return array
     */
    public function parse(string $token): array ;
}
