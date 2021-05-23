<?php

namespace Bermuda\Authentication\Adapter;

use Bermuda\Authentication\Utils;
use Dflydev\FigCookies\SetCookie;
use Bermuda\Authentication\Result;
use Fig\Http\Message\RequestMethodInterface;
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
    /**
     * @var callable
     */
    private $dateTimeFactory;
    private array $cookieParams;
    
    public function __construct(UserProviderInterface $provider, callable $responseGenerator, array $cookieParams = [])
    {
        $this->cookieParams = $cookieParams;
        $this->dateTimeFactory = static fn(): \DateTimeInterface => new \DateTimeImmutable();
        parent::__construct($provider, $responseGenerator);
    }
    
    public function setDateTimeFactory(callable $factory): self
    {
        $this->dateTimeFactory = static fn(): \DateTimeInterface => $factory();
        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    protected function authenticateRequest(ServerRequestInterface $request): Result
    {
        if (null != ($id = $this->getIdFromRequest($request)))
        {
            if (($user = $this->provider->provide($id)) != null)
            {
                return $this->forceAuthentication($request, $user, $this->viaRemember($request));
            }
        }
      
        return Result::unauthorized();
    }

    /**
     * @inheritDoc
     */
    public function write(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (!$this->cookieExists($request))
        {
            if (self::$user instanceof UserInterface)
            {
                if (!$this->viaRemember($request))
                {
                    return FigResponseCookies::set($response, $this->setCookie($this->getSID($user)));
                }

                return FigResponseCookies::set($response, $this->setCookie($this->getSID($user))->rememberForever());
            }

            return $response;
        }

        return self::$user == null ? $this->clear($response) : $response;
    }

    /**
     * @param string $value
     * @return SetCookie
     */
    private function setCookie(string $value = ''): SetCookie
    {
        return SetCookie::create($this->getCookieName(), $value)
            ->withHttpOnly($this->cookieParams['httpOnly'] ?? true)
            ->withSecure($this->cookieParams['secure'] ?? false)
            ->withPath($this->cookieParams['path'] ?? '/')
            ->withExpires($this->cookieParams['lifetime'] ?? null);
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
}
