<?php


namespace App\Authentication;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


/**
 * Class AuthenticationMiddleware
 * @package App\Authentication
 */
final class AuthenticationMiddleware implements MiddlewareInterface
{
    private AdapterInterface $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->adapter->write($request = $this->adapter->authenticate($request), $handler->handle($request));
    }
}