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
    public const user_at = 'Bermuda\Authentication\AdapterInterface@user_at';
    
    /**
     * @param ServerRequestInterface $request
     * @param UserInterface|null $user
     * @param bool|null $remember
     * @return Result
     */
    public function authenticate(ServerRequestInterface $request, ?UserInterface $user = null, ?bool $remember = null): Result;

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
