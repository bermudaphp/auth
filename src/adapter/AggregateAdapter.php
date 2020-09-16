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
    private AdapterInterface $delegate;

    /**
     * @param AdapterInterface $delegate
     * @param AdapterInterface[] $adapters
     */
    public function __construct(AdapterInterface $delegate, array $adapters = [])
    {
        $this->delegate = $delegate;
        
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
        $this->adapters[get_class($adapter)] = $adapter;
        return $this;
    }
    
    /**
     * @param string $classname
     * @return AdapterInterface|null
     */
    public function getAdapter(string $classname):? AdapterInterface
    {
        if (get_class($this->delegate) == $classname)
        {
            return $this->delegate;
        }
        
        return $this->adapter ?? null;
    }
    
    /**
     * @param string $classname
     * @return bool
     */
    public function hasAdapter(string $classname): bool
    {
        return $this->getAdapter($classname) != null;
    }
    
    /**
     * @return AdapterInterface
     */
    public function getDelegate(): AdapterInterface
    {
        return $this->delegate;
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
            $request = $adapter->authenticate($request, $user, $remember);
            
            if (($result = $request->getAttribute(self::request_result_at))
                ->isFailure() || $result->isAuthorized())
            {
                $this->addAdapter($this->delegate)
                    ->delegate = $adapter;
                
                return $request;
            }
        }
        
        return $this->delegate->authenticate($request, $user, $remember);
    }
    
    
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function unauthorized(ServerRequestInterface $request): ResponseInterface
    {
         return $this->delegate->unauthorized($request);
    }
    
    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function clear(ResponseInterface $response): ResponseInterface 
    {
         return $this->delegate->clear($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function write(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
         return $this->delegate->write($request, $response);
    }
}
