<?php


namespace App;


/**
 * Interface TokenParserInterface
 * @package App
 */
interface TokenParserInterface
{
    /**
     * @param string $token
     * @return array
     */
    public function parse(string $token): array ;
}