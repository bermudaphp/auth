<?php


namespace Bermuda\Authentication;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


/**
 * Class GuardMiddleware
 * @package Bermuda\Authentication
 */
final class GuardMiddleware implements MiddlewareInterface
{
    private AdapterInterface $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getAttribute(AdapterInterface::request_user_attribute) instanceof UserInterface)
        {
            return $handler->handle($request);
        }

        return $this->adapter->unauthorized($request);
    }
}
