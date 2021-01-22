<?php

namespace Bermuda\Authentication;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class AuthMiddleware
 * @package Bermuda\Authentication
 */
final class AuthMiddleware implements MiddlewareInterface, AuthServiceInterface
{
    private AdapterInterface $adapter;
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
        return $this->adapter->write($request = $this->authenticate($request), $handler->handle($request));
    }
    
    /**
     * @inheritDoc
     */
    public function authenticate(ServerRequestInterface $request): ServerRequestInterface
    {
        $request = $this->adapter->authenticate($request);
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
    
    private function getResultFromRequest(ServerRequestInterface $req): Result
    {
        return $request->getAttribute(AdapterInterface::resultAt);
    }
}
