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

    private ?SessionRepositoryInterface $sessionRepository;

    public function __construct(AdapterInterface $adapter, ?SessionRepository $repository = null)
    {
        $this->adapter = $adapter;
        $this->sessionRepository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $this->getResultFromRequest($request = $this->authenticate($request));
        $response = $handler->handle($request);

        if ($this->user != null)
        {
            $request = $this->authenticate($request, self::$user, self::$remember);
        }

        return $this->adapter->write($request, $response);
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
            if ($this->sessionRepository == null)
            {
                throw new \RuntimeException('Bermuda\Authentication\AuthenticationMiddleware::$sessionRepository is null');
            }
            
            if (($id = $this->getIdFromRequest($request)) != null && 
                ($session = $user->sessions()->get($id)) != null)
            {
                $session->activity(($this->dateTimeFactory)());
            }
            
            else
            {
                $user->sessions()->add($session = $this->sessionRepository->make($user, $request));
                $user->sessions()->setCurrentId($session->getId());
            }
            
            $this->sessionRepository->store($session);
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
}
