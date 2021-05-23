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

    public static ?UserInterface $user = null;
    
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
        return $this->adapter->write($this->authenticate($request), $response = $handler->handle($request));
    }

    /**
     * @param ServerRequestInterface $request
     * @param UserInterface $user
     * @param bool $remember
     * @return ServerRequestInterface
     */
    public function authenticate(ServerRequestInterface $request, UserInterface $user, bool $remember = false): ServerRequestInterface
    {
        if (($result = $this->adapter->authenticate($request, $user, $remember))->isAuthorized())
        {
            $request = $request->withAttribute($this->adapter::user_at, self::$user = $result->getUser());

            if (self::$user instanceof SessionAwareInterface)
            {
                if ($this->storage == null)
                {
                    throw new \RuntimeException('Bermuda\Authentication\SessionStorageInterface instance not set. Call '. __CLASS__ . '::storage()');
                }

                if (($session = self::$user->sessions()->current()) != null)
                {
                    $session->activity(new \DateTimeImmutable());
                    $this->storage->store($session);
                }
            }
        }

        return $request;
    }

    public static function isAuthorized(): bool
    {
        return self::$user != null;
    }

    public static function getUser():? UserInterface
    {
        return self::$user;
    }
}
