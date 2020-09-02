<?php


namespace App\Authentication;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Interface AdapterInterface
 * @package App\Auth
 */
interface AdapterInterface
{
    public const request_user_at = UserInterface::class;
    public const request_result_at = Result::class;
    public const request_remember_at = self::class . '::remember_at';

    /**
     * @param ServerRequestInterface $request
     * @param UserInterface|null $user
     * @param bool $remember
     * @return ServerRequestInterface
     */
    public function authenticate(ServerRequestInterface $request, UserInterface $user = null, bool $remember = false): ServerRequestInterface;

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function unauthorized(ServerRequestInterface $request): ResponseInterface ;

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
