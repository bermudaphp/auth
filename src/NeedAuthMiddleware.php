<?php

namespace Bermuda\Authentication;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * Class NeedAuthMiddleware
 * @package Bermuda\Authentication
 */
final class NeedAuthMiddleware implements MiddlewareInterface
{
    private AdapterInterface $adapter;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(AdapterInterface $adapter, ResponseFactoryInterface $responseFactory)
    {
        $this->adapter = $adapter;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getAttribute($this->adapter::user_at) instanceof UserInterface)
        {
            return $handler->handle($request);
        }

        return $this->adapter->unauthorized($request, $this->responseFactory->createResponse(401));
    }
}
