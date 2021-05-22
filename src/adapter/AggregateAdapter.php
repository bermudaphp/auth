<?php

namespace Bermuda\Authentication\Adapter;

use Bermuda\String\Str;
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
final class PasswordAdapter extends AbstractAdapter
{
    private string $identity;
    private string $credential;
    private string $path = 'login';
    private string $remember = 'remember_me';
    
    /**
     * @var callable
     */ 
    private $checkCridentialCallback;
    
    public function __construct(
        UserProviderInterface $provider, 
        callable $responseGenerator, 
        string $identity = 'username', string $credential = 'password')
    {
        parent::__construct($provider, $responseGenerator);
        $this->identity = $identity, $this->credential = $credential;
        $this->checkCridentialCallback = static fn(UserInterface $user, string $credential): bool => \password_verify($credential, $user->getCredential());
    }
    
    public function credential(?string $value = null): string
    {
        return $value ? $this->credential = $value : $this->credential;
    }
    
    public function remember(?string $value = null): string
    {
        return $value ? $this->remember_me = $value : $this->remember_me;
    }
    
    public function identity(?string $value = null): string
    {
        return $value ? $this->identity = $value : $this->identity;
    }
    
    public function path(string $value = null): string
    {
        return $value ? $this->path = $value : $this->path;
    }
    
    public function checkCredentialCallback(?callable $value = null): callable
    {
        return $value ? $this->checkCridentialCallback = static fn(UserInterface $user, string $credential):bool 
            => (bool) $value($user, $credential) : $this->checkCridentialCallback;
    }
    
    protected function authenticateRequest(ServerRequestInterface $request): Result
    {
        if (Str::equals($request->getMethod(), 'POST') && 
             Str::equals($request->getUri()->getPath(), $this->path))
        {
            if (($id = $this->getIdFromRequest($request)) != null 
                && ($user = $this->provider->provide($id)) != null)
            {
                
                $credential = ((array) $request->getParsedBody())[$this->credential] ?? null;
                
                if ($credential == null)
                {
                    return Result::failure('Credential is missing');
                }
                
                $result = ($this->checkCridentialCallback)($user, $credential);
                
                if ($result)
                {
                    return $this->forceAuthentication($user, $this->viaRemember($request));
                }
                
                return Result::failure('Invalid credential');
            }
        }
         
        return Result::unauthorized();
    }
    
    /**
     * @param ServerRequestInterface $request
     * @return UserInterface|null
     */
    protected function getIdFromRequest(ServerRequestInterface $request):? string 
    {
        return ((array) $request->getParsedBody())[$this->identity] ?? null;
    }
    
    protected function viaRemember(ServerRequestInterface $request): bool
    {
        return Str::equalsAny((string) ((array) $request->getParsedBody())[$this->remember_me] ?? '', ['on', '1']);
    }
}
