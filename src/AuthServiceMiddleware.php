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

    private static bool $remember = false;
    private static ?UserInterface $user = null;

    private ?SessionRepositoryInterface $repository;

    public function __construct(AdapterInterface $adapter, ?SessionRepositoryInterface $repository = null)
    {
        $this->adapter = $adapter;
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $this->getResultFromRequest($request = $this->authenticate($request));
        $response = $handler->handle($request);

        if (self::isAuthenticated())
        {
            $request = $this->authenticate($request, self::$user, self::$remember);
        }

        return $this->adapter->write($request, $response);
    }
    
    public function repository(?SessionRepositoryInterface $repository = null):? SessionRepositoryInterface
    {
        if ($repository != null)
        {
            $this->repository = $repository;
        }
        
        return $this->repository;
    }

    /**
     * @param UserInterface $user
     * @param bool $remember
     */
    public static function auth(UserInterface $user, bool $remember = false): void
    {
        self::$user = $user; self::$remember = $remember;
    }
    
    public static function isAuthenticated(): bool
    {
        return self::$user != null;
    }
    
    /**
     * @param UserInterface $user
     * @param bool $remember
     */
    public static function logout(): void
    {
        self::$user = null; self::$remember = null;
    }
    
    public static function user():? UserInterface
    {
        return self::$user;
    }

    /**
     * @param ServerRequestInterface $request
     * @param UserInterface|null $user
     * @param bool $remember
     * @return ServerRequestInterface
     */
    private function authenticate(ServerRequestInterface $request, ?UserInterface $user = null, bool $remember = false): ServerRequestInterface
    {
        $request = $this->adapter->authenticate($request, $user, $remember);
        $result  = $this->getResultFromRequest($request);
        
        if ($result->isAuthorized() && ($user = $result->getUser()) 
            instanceof SessionAwareInterface)
        {
            if ($this->repository == null)
            {
                throw new \RuntimeException('Bermuda\Authentication\SessionRepositoryInterface instance not set. Call '. __CLASS__ . '::repository()');
            }
            
            if (($session = $user->sessions()->current()) != null)
            {
                $session->activity($this->getCurrentTime());
                $this->repository->store($session);
            }
        }
        
        return $request;
    }

    /**
     * @param ServerRequestInterface $req
     * @return Result
     */
    private function getResultFromRequest(ServerRequestInterface $req): Result
    {
        return $req->getAttribute(AdapterInterface::resultAt);
    }
    
    private function getCurrentTime(): \DateTimeInterface
    {
        return new \DateTimeImmutable();
    }
}
