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
    public const userAt = UserInterface::class;
    public const resultAt = Result::class;
    public const rememberAt = self::class . '::rememberAt';

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
