<?php


namespace Bermuda\Authentication\Adapter;


use Dflydev\FigCookies\SetCookie;
use Bermuda\Authentication\Result;
use Psr\Http\Message\ResponseInterface;
use Bermuda\Authentication\UserInterface;
use Dflydev\FigCookies\FigResponseCookies;
use Psr\Http\Message\ServerRequestInterface;
use Bermuda\Authentication\SessionAwareInterface;
use Bermuda\Authentication\UserProviderInterface;


/**
 * Class CookieAdapter
 * @package Bermuda\Authentication\Adapter
 */
class CookieAdapter extends AbstractAdapter
{
    protected array $cookieParams;
    protected $dateTimeFactory;
    
    const CONFIG_COOKIE_KEY = 'cookie';
    const CONFIG_DATETIME_FACTORY_KEY = 'datetime_factory';

    public function __construct(UserProviderInterface $provider, callable $responseGenerator, 
        array $config = [], ?SessionRepositoryInterface $repository = null)
    {
        $this->cookieParams = $config[self::CONFIG_COOKIE_KEY] ?? [];
        
        if (array_key_exists(self::CONFIG_DATETIME_FACTORY_KEY, $config))
        {
            $dateTimeFactory = $config[self::CONFIG_DATETIME_FACTORY_KEY];
            $this->dateTimeFactory = static function() use ($dateTimeFactory): \DateTimeInterface
            {
                return $datetimeFactory() ?? new \DateTimeImmutable();
            };
        }
        
        else
        {
            $this->dateTimeFactory = $config[self::CONFIG_DATETIME_FACTORY_KEY] ?? static function(): \DateTimeInterface
            {
                return new \DateTime();
            };
        }
        
        parent::__construct($provider, $responseGenerator, $repository);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param UserInterface $user
     * @param bool $remember
     * @return ServerRequestInterface
     */
    protected function forceAuthentication(ServerRequestInterface $request, UserInterface $user, bool $remember = false): ServerRequestInterface
    {
        if ($user instanceof SessionAwareInterface)
        {
            if (!$this->repository)
            {
                throw new RuntimeException('Bermuda\Authentication\SessionRepositoryInterface instance is missing');
            }
            
            if (($id = $this->getIdFromRequest($request)) != null && ($session = $user->sessions()->get($id)) != null)
            {
                $session->activity(($this->dateTimeFactory)());
            }
            
            else
            {
                $user->sessions()->add($session = $this->repository->make($user, $request));
                $user->sessions()->setCurrentId($session->getId());
            }
            
            $this->repository->store($session);
            
        } 
        
        return Result::authorized($request, $user, $remember);
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
     * @inheritDoc
     */
    public function write(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (!$this->cookieExists($request))
        {
            if (($user = $this->getUserFromRequest($request)) == null)
            {
                return $response;
            }

            if (!$this->viaRemember($request))
            {
                return FigResponseCookies::set($response, $this->setCookie($this->getSID($user)));
            }

            return FigResponseCookies::set($response, $this->setCookie($this->getSID($user))->rememberForever());
        }

        if (($user = $this->getUserFromRequest($request)) == null)
        {
            return $this->clear($response);
        }

        return $response;
    }

    /**
     * @param ServerRequestInterface $req
     * @return bool
     */
    protected function viaRemember(ServerRequestInterface $req): bool
    {
        return $req->getAttribute(self::request_remember_at, false);
    }

    /**
     * @param string $value
     * @return SetCookie
     */
    protected function setCookie(string $value = ''): SetCookie
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
    protected function getCookieName(): string
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
        return ($user = $request->getAttribute(self::request_user_at)) instanceof UserInterface ? $user : null;
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
