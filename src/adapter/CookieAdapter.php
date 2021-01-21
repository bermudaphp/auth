<?php

namespace Bermuda\Authentication\Adapter;

use Dflydev\FigCookies\SetCookie;
use Bermuda\Authentication\Result;
use Psr\Http\Message\ResponseInterface;
use Bermuda\Authentication\UserInterface;
use Dflydev\FigCookies\FigResponseCookies;
use Psr\Http\Message\ServerRequestInterface;
use Bermuda\Authentication\AdapterInterface;
use Bermuda\Authentication\SessionAwareInterface;
use Bermuda\Authentication\UserProviderInterface;

/**
 * Class CookieAdapter
 * @package Bermuda\Authentication\Adapter
 */
final class CookieAdapter extends AbstractAdapter
{
    private $dateTimeFactory;
    private array $cookieParams;
    
    const CONFIG_COOKIE_KEY = 'CookieAdapter:cookie';
    const CONFIG_DATETIME_FACTORY_KEY = 'CookieAdapter:datetimeFactory';
    const CONFIG_USER_PROVIDER_KEY = 'CookieAdapter:userProvider';
    const CONFIG_RESPONSE_GENERATOR_KEY = 'CookieAdapter:responseGenerator';

    public function __construct(array $config)
    {
        $this->cookieParams = $config[self::CONFIG_COOKIE_KEY] ?? [];
        $this->dateTimeFactory = static function() use ($dateTimeFactory): \DateTimeInterface
        {
            if (!$dateTimeFactory)
            {
                return new \DateTimeImmutable();
            }
                
            return $datetimeFactory();
        };
        
        parent::__construct($config[self::CONFIG_USER_PROVIDER_KEY], $config[self::CONFIG_RESPONSE_GENERATOR_KEY]);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    protected function authenticateRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        if (null != ($id = $this->getIdFromRequest($request)))
        {
            if (($user = $this->provider->provide($id)) != null)
            {
                return $this->forceAuthentication($request, $user, $this->viaRemember($request));
            }
        }
      
        return Result::unauthorized($request);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function unauthorized(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->delegate && $request->getAttribute(self::request_result_at)->isFailure())
        {
            return $this->delegate->unauthorized($request);
        }
        
        return ($this->responseGenerator)($request);
    }

    /**
     * @inheritDoc
     */
    public function write(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user = $this->getUserFromRequest($request);
        
        if (!$this->cookieExists($request))
        {
            if ($user != null)
            {
                if (!$this->viaRemember($request))
                {
                    return FigResponseCookies::set($response, $this->setCookie($this->getSID($user)));
                }

                return FigResponseCookies::set($response, $this->setCookie($this->getSID($user))->rememberForever());
            }

            return $response;
        }

        return $user == null ? $this->clear($response) : $response;
    }

    /**
     * @param ServerRequestInterface $req
     * @return bool
     */
    protected function viaRemember(ServerRequestInterface $req): bool
    {
        return $req->getAttribute(self::rememberAt, false);
    }

    /**
     * @param string $value
     * @return SetCookie
     */
    private function setCookie(string $value = ''): SetCookie
    {
        return SetCookie::create($this->getCookieName(), $value)
            ->withHttpOnly($this->cookieParams['httpOnly'] ?? true)
            ->withSecure($this->cookieParams['secure'] ?? true)
            ->withPath($this->cookieParams['path'] ?? '/')
            ->withExpires($this->cookieParams['lifetime'] ?? null);
    }

    /**
     * @return string
     */
    private function getCookieName(): string
    {
        return $this->cookieParams['name'] ?? '_sid';
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    private function cookieExists(ServerRequestInterface $request): bool
    {
        return isset($request->getCookieParams()[$this->getCookieName()]);
    }

    /**
     * @param ServerRequestInterface $request
     * @return UserInterface|null
     */
    private function getUserFromRequest(ServerRequestInterface $request):? UserInterface
    {
        return ($user = $request->getAttribute(self::userAt)) instanceof UserInterface ? $user : null;
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function clear(ResponseInterface $response): ResponseInterface
    {
        return FigResponseCookies::set($response, $this->setCookie()->expire());
    }

    /**
     * @param ServerRequestInterface $request
     * @return UserInterface|null
     */
    protected function getIdFromRequest(ServerRequestInterface $request):? string
    {
        return $request->getCookieParams()[$this->getCookieName()] ?? null;
    }
}
