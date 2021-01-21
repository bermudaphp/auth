<?php

namespace Bermuda\Authentication\Adapter;

use Bermuda\Authentication\Result;
use Bermuda\Authentication\UserInterface;
use Bermuda\Authentication\AdapterInterface;
use Bermuda\Authentication\UserProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Bermuda\Authentication\SessionRepositoryInterface;

/**
 * Class AbstractAdapter
 * @package Bermuda\Authentication\Adapter
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * @var string[]
     */
    protected array $messages = [];
    protected \Closure $responseGenerator;
    protected UserProviderInterface $provider;

    public function __construct(UserProviderInterface $provider, callable $responseGenerator)
    {
        $this->provider = $provider;
        $this->setResponseGenerator($responseGenerator);
    }
    
    /**
     * @param UserProviderInterface|null $provider
     * @return static
     */
    public function provider(?UserProviderInterface $provider = null): UserProviderInterface
    {
        if ($provider != null)
        {
            $this->provider = $provider;
        }
        
        return $this->provider;
    }
    
    /**
     * @param callable $responseGenerator
     * @return static
     */
    public function setResponseGenerator(callable $responseGenerator): self
    {
        $this->responseGenerator = static function (ServerRequestInterface $req) use ($responseGenerator): ResponseInterface
        {
            return $responseGenerator($req);
        };
        
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
        if ($user != null)
        {
            return $this->forceAuthentication($request, $user, $remember);
        }

        return $this->authenticateRequest($request);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    protected function authenticateRequest(ServerRequestInterface $request): ServerRequestInterface 
    {
        return Result::unauthorized($request);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param UserInterface $user
     * @param bool $remember
     * @return ServerRequestInterface
     */
    protected function forceAuthentication(ServerRequestInterface $request, UserInterface $user, bool $remember = false): ServerRequestInterface
    {
        return Result::authorized($request, $user, $remember);
    }
    
    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function clear(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }
    
     /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function write(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
         return $response;
    }
    
    /**
     * @param ServerRequestInterface $request
     * @return UserInterface|null
     */
    protected function getIdFromRequest(ServerRequestInterface $request):? string 
    {
        return null;
    }
    
    /**
     * @param UserInterface $user
     * @return string
     */
    protected function getSID(UserInterface $user): string
    {
        if ($user instanceof SessionAwareInterface)
        {
            return $user->sessions()->current()->getId();
        }

        return $user->getId();
    }
    
    /**
     * @param array $messages
     * @return array
     */
    public function messages(array $messages = []): array
    {
        if ($messages != [])
        {
            $this->messages = array_merge($this->messages, $messages);
        }

        return $this->messages;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function unauthorized(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->responseGenerator)($request);
    }
    
    /**
     * @param int $code
     * @return string
     */
    protected function getMessage(int $code): string
    {
        return $this->messages[$code] ?? 'Authentication failed!';
    }
}
