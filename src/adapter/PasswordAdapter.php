<?php

namespace Bermuda\Authentication\Adapter;

use Bermuda\String\Str;
use Bermuda\Authentication\Result;
use Bermuda\Authentication\UserInterface;
use Bermuda\Authentication\AdapterInterface;
use Bermuda\Authentication\UserProviderInterface;
use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PasswordAdapter
 * @package Bermuda\Authentication\Adapter
 */
final class PasswordAdapter extends AbstractAdapter
{
    private string $identity;
    private string $credential;
    private string $path = '/login';
    private string $remember_me = 'remember_me';
    
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
        $this->identity = $identity; $this->credential = $credential;
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
        if (Str::equals($request->getMethod(), RequestMethodInterface::METHOD_POST) &&
             Str::equals($request->getUri()->getPath(), $this->path))
        {
            if (($id = $this->getIdFromRequest($request)) == null)
            {
                return Result::failure($this->messages[Result::IDENTITY_IS_MISSING], Result::IDENTITY_IS_MISSING);
            }
            
            if (($user = $this->provider->provide($id)) != null)
            {
                if (($credential = ((array) $request->getParsedBody())[$this->credential] ?? null) == null)
                {
                    return Result::failure($this->messages[Result::CREDENTIAL_IS_MISSING], Result::CREDENTIAL_IS_MISSING);
                }

                if (($this->checkCridentialCallback)($user, $credential))
                {
                    return $this->forceAuthentication($user, $this->viaRemember($request));
                }
                
                return Result::failure($this->messages[Result::CREDENTIAL_IS_INVALID], Result::CREDENTIAL_IS_INVALID);
            }
            
            return Result::failure($this->messages[Result::IDENTITY_NOT_FOUND], Result::IDENTITY_NOT_FOUND);
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
        return self::$viaRemember || Str::equalsAny(((array) $request->getParsedBody())[$this->remember_me] ?? '', ['on', '1']);
    }
}
