<?php

namespace Bermuda\Authentication;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class AuthServiceMiddleware
 * @package Bermuda\Authentication
 */
final class AuthServiceMiddleware implements MiddlewareInterface
{
    private AdapterInterface $adapter;
    private ?SessionStorageInterface $storage;
    
    public const user_at = 'Bermuda\Authentication\AuthServiceMiddleware@user_at';

    public function __construct(AdapterInterface $adapter, ?SessionStorageInterface $storage = null)
    {
        $this->adapter = $adapter; $this->storage = $storage;
    }
    
    public function storage(?SessionStorageInterface $storage = null):? SessionStorageInterface
    {
        return $storage ? $this->storage = $storage : $this->storage;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (($result = $this->authenticate($request))->isAuthorized())
        {
            $request = $request->withAttribute(self::user_at, $user = $result->getUser());
            
            if ($user instanceof SessionAwareInterface)
            {
                if ($this->storage == null)
                {
                    throw new \RuntimeException('Bermuda\Authentication\SessionStorageInterface instance not set. Call '. __CLASS__ . '::storage()');
                }
            
                if (($session = $user->sessions()->current()) != null)
                {
                    $session->activity(new \DateTimeImmutable());
                    $this->storage->store($session);
                }
            }
        }

        return $this->adapter->write($response = $handler->handle($request), $response);
    }
}
