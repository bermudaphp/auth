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
    protected ?AdapterInterface $next = null;
    
    public const CONFIG_USER_PROVIDER_KEY = 'AbstractAdapter:userProvider';
    public const CONFIG_RESPONSE_GENERATOR_KEY = 'AbstractAdapter:responseGenerator';

    public function __construct(array $config)
    {
        $this->provider = $config[self::CONFIG_USER_PROVIDER_KEY];
        $this->setResponseGenerator($config[self::CONFIG_RESPONSE_GENERATOR_KEY]);
    }
    
    public function setNext(AdapterInterface $adapter): AdapterInterface
    {
        return $this->next = $adapter;
    }
    
    public function provider(?UserProviderInterface $provider = null): UserProviderInterface
    {
        return $pdovider ? $this->provider = $provider : $this->provider;
    }
    
    /**
     * @param callable $responseGenerator
     * @return static
     */
    public function setResponseGenerator(callable $responseGenerator): self
    {
        $this->responseGenerator = static fn(ServerRequestInterface $req): ResponseInterface => $responseGenerator($req);
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public final function authenticate(ServerRequestInterface $request, ?UserInterface $user = null, ?bool $remember = null): Result
    {
        $result = $user != null ? $this->forceAuthentication($request, $user, $remember = $remember ?? $this->viaRemember($request)) : $this->authenticateRequest($request);
        return $this->next !== null && !($result->isAuthorized() || $result->isFailure()) ? $this->next->authenticate($request, $user, $remember) : $result;
    }
    
    protected function authenticateRequest(ServerRequestInterface $request): Result
    {
        return Result::unauthorized();
    }

    protected function forceAuthentication(ServerRequestInterface $request, UserInterface $user, bool $remember = false): Result
    {
        return Result::authorized($user);
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
            return $user->sessions()->current()->id();
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
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function unauthorized(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return ($this->responseGenerator)($request, $response);
    }
    
    protected function viaRemember(ServerRequestInterface $request): bool
    {
        return false;
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
