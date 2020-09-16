<?php


namespace Bermuda\Authentication\Adapter;


use Bermuda\Authentication\Result;
use Bermuda\Authentication\UserInterface;
use Bermuda\Authentication\AdapterInterface;
use Bermuda\Authentication\UserProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Class Adapter
 * @package Bermuda\Authentication\Adapter
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * @var callable
     */
    protected $responseGenerator;
    protected UserProviderInterface $provider;
    protected ?SessionRepositoryInterface $repository;

    /**
     * @var string[]
     */
    protected array $messages = [];

    public function __construct(UserProviderInterface $provider, callable $responseGenerator, 
        ?SessionRepositoryInterface $repository = null)
    {
        $this->provider = $provider;
        $this->setResponseGenerator($responseGenerator);
        $this->repository = $repository;
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
     * @param SessionRepositoryInterface|null $repository
     * @return SessionRepositoryInterface
     */
    public function repository(SessionRepositoryInterface $repository = null):? SessionRepositoryInterface
    {
        if ($repository)
        {
            $this->repository = $repository;
        }
        
        return $this->repository;
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
    abstract protected function authenticateRequest(ServerRequestInterface $request): ServerRequestInterface ;
    
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
    
    protected function getMessage(int $code): string
    {
        return $this->messages[$code] ?? 'Authentication failed!';
    }
}
