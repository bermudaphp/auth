<?php


namespace Bermuda\Authentication;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


/**
 * Class AuthenticationMiddleware
 * @package Bermuda\Authentication
 */
final class AuthenticationMiddleware implements MiddlewareInterface
{
    private AdapterInterface $adapter;
    private ?SessionRepositoryInterface $repository;

    public function __construct(AdapterInterface $adapter, ?SessionRepository $repository = null)
    {
        $this->adapter = $adapter;
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $this->adapter->authenticate($request);
        $result  = $this->getResultFromRequest($request);
        
        if ($result->isAuthorized() && ($user = $result->getUser()) 
            instanceof SessionAwareInterface)
        {
            if ($this->repository == null)
            {
                throw new \RuntimeException(SessionRepositoryInterface::class . ' instance is null');
            }
            
            if (($id = $this->getIdFromRequest($request)) != null && 
                ($session = $user->sessions()->get($id)) != null)
            {
                $session->activity(($this->dateTimeFactory)());
            }
            
            else
            {
                $user->sessions()->add($session = $this->repository->make($user, $request));
                $user->sessions()->setCurrentId($session->getId());
            }
            
            $this->repository->store($session);
            
        }
        
        return $this->adapter->write($request, $handler->handle($request));
    }
    
    private function getResultFromRequest(ServerRequestInterface $req): Result
    {
        return $request->getAttribute(AdapterInterface::resultAt);
    }
}
