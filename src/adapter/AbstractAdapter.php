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
    protected array $messages = [
        Result::FAILURE => 'Authorization failed!',
        Result::CREDENTIAL_IS_MISSING => 'Credential is missing!',
        Result::CREDENTIAL_IS_INVALID => 'Credential is invalid!',
        Result::IDENTITY_IS_MISSING => 'Identity is missing!',
        Result::IDENTITY_NOT_FOUND => 'Identity not found!'
    ];
    
    protected \Closure $responseGenerator;
    protected UserProviderInterface $provider;
    protected ?AdapterInterface $next = null;

    protected static ?UserInterface $user = null;
    protected static bool $viaRemember = false;
    
    public function __construct(UserProviderInterface $provider, callable $responseGenerator)
    {
        $this->provider = $provider;
        $this->setResponseGenerator($responseGenerator);
    }
    
    final public function setNext(AdapterInterface $adapter): AdapterInterface
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
        $result = $user != null ? $this->forceAuthentication(static::$user = $user, static::$viaRemember = $remember ?? $this->viaRemember($request)) : $this->authenticateRequest($request);
        return $this->next !== null && !($result->isAuthorized() || $result->isFailure()) ? $this->next->authenticate($request, $user, $remember) : $result;
    }
    
    protected function authenticateRequest(ServerRequestInterface $request): Result
    {
        return Result::unauthorized();
    }

    protected function forceAuthentication(UserInterface $user, bool $remember = false): Result
    {
        return Result::authorized($user);
    }
    
    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function clear(ResponseInterface $response): ResponseInterface
    {
        return $this->next != null ? $this->next->clear($response) : $response;
    }
    
     /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function write(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->next != null ? $this->next->write($request, $response) : $response;
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
        return $user instanceof SessionAwareInterface 
            ? $user->sessions()->current()->getId() : $user->getId();
    }
    
    /**
     * @param array|null $messages
     * @return array
     */
    public function messages(?array $messages = null): array
    {
        return $messages != null ? $this->messages = array_merge($this->messages, $messages) : $this->messages ;
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
        return self::$viaRemember;
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
