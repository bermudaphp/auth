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
    public function __construct(array $adapters = [])
    {
        foreach ($adapters as $adapter)
        {
            $this->addAdapter($adapter);
        }
    }
    
    /**
     * @param AdapterInterface $adapter
     * @return self
     */
    public function addAdapter(AdapterInterface $adapter): self
    {
        $this->adapters[] = $adapter;
        return $this;
    }
     
    /**
     * @param ServerRequestInterface $request
     * @param UserInterface|null $user
     * @param bool $remember
     * @return ServerRequestInterface
     */
    public function authenticate(ServerRequestInterface $request, UserInterface $user = null, bool $remember = false): ServerRequestInterface
    {
        
    }
    
    
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function unauthorized(ServerRequestInterface $request): ResponseInterface
    {
        
    }
    
    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function clear(ResponseInterface $response): ResponseInterface 
    {    
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function write(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
    }
}
