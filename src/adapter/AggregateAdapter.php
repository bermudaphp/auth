<?php

namespace Bermuda\Authentication\Adapter;

use Bermuda\Authentication\Result;
use Bermuda\Authentication\UserInterface;
use Bermuda\Authentication\AdapterInterface;
use Bermuda\Authentication\UserProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AggregateAdapter
 * @package Bermuda\Authentication\Adapter
 */
final class AggregateAdapter implements AdapterInterface
{
    /**
     * @var AdapterInterface[]
     */
    private array $adapters = [];

    /**
     * @param AdapterInterface[] $adapters
     */
    public function __construct(iterable $adapters = [])
    {
        $this->addAdapters($adapters);
    }
    
    /**
     * @param AdapterInterface $adapter
     * @return self
     */
    public function addAdapter(AdapterInterface $adapter): self
    {
        $this->adapters[get_class($adapter)] = $adapter;
        return $this;
    }
    
     /**
     * @param AdapterInterface[] $adapters
     * @return self
     */
    public function addAdapters(iterable $adapters): self
    {
        foreach($adapters as $adapter)
        {
            $this->addAdapter($adapter);
        }
        
        return $this;
    }
    
    /**
     * @param string $classname
     * @return AdapterInterface|null
     */
    public function getAdapter(string $classname):? AdapterInterface
    {
        return $this->adapters[$classname] ?? null;
    }
    
    /**
     * @param string $classname
     * @return bool
     */
    public function hasAdapter(string $classname): bool
    {
        return isset($this->adapters[$classname]);
    }
     
    /**
     * @param ServerRequestInterface $request
     * @param UserInterface|null $user
     * @param bool $remember
     * @return ServerRequestInterface
     */
    public function authenticate(ServerRequestInterface $request, UserInterface $user = null, bool $remember = false): ServerRequestInterface
    {
        foreach ($this->adapters as $adapter)
        {
            $result = ($request = $adapter->authenticate($request, $user, $remember))
                ->getAttribute(self::resultAt);
            
            if ($result->isAuthorized() || $result->isFailure())
            {
                return $request;
            }
        }
        
        return Result::unauthorized($request);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function unauthorized(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $original = $response;
        
        foreach($this->adapters as $adapter)
        {
            $response = $adapter->unauthorized($request, $response);
            
            if ($original !== $response)
            {
                return $response;
            }
        }
        
        return $response->withStatus(401, 'Unauthorized');
    }
    
    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function clear(ResponseInterface $response): ResponseInterface 
    {
        $original = $response;
        
        foreach($this->adapters as $adapter)
        {
            $response = $adapter->clear($response);
            
            if ($original !== $response)
            {
                return $response;
            }
        }
        
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function write(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $original = $response;
       
        foreach($this->adapters as $adapter)
        {
            $response = $adapter->write($request, $response);
            
            if ($original !== $response)
            {
                return $response;
            }
        }
        
        return $response;
    }
}
