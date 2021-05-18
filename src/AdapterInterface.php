<?php

namespace Bermuda\Authentication;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface AdapterInterface
 * @package Bermuda\Authentication
 */
interface AdapterInterface
{
    public const userAt = 'Bermuda\Authentication\AdapterInterface:userAt';
    public const resultAt = 'Bermuda\Authentication\AdapterInterface:resultAt';
    public const rememberAt = 'Bermuda\Authentication\AdapterInterface:rememberAt';
    public const CONFIG_ID = 'Bermuda\Authentication\AdapterInterface:configID';

    /**
     * @param ServerRequestInterface $request
     * @param UserInterface|null $user
     * @param bool $remember
     * @return ServerRequestInterface
     */
    public function authenticate(ServerRequestInterface $request, UserInterface $user = null, bool $remember = false): ServerRequestInterface;

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function unauthorized(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface ;

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function clear(ResponseInterface $response): ResponseInterface ;

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function write(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface ;
}
