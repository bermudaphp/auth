<?php


namespace App\Authentication\Adapter;


use App\Authentication\Result;
use Dflydev\FigCookies\SetCookie;
use App\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Dflydev\FigCookies\FigResponseCookies;
use Psr\Http\Message\ServerRequestInterface;
use App\Authentication\SessionAwareInterface;
use App\Authentication\UserProviderInterface;


/**
 * Class CookieAdapter
 * @package App\Authentication\Adapter
 */
class CookieAdapter extends AbstractAdapter
{
    protected array $cookieParams;

    public function __construct(UserProviderInterface $provider, callable $responseGenerator, array $cookieParams = [])
    {
        $this->cookieParams = $cookieParams;
        parent::__construct($provider, $responseGenerator);
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
                if ($user instanceof SessionAwareInterface)
                {
                    $user->sessions()->setCurrentId($id);
                }

                return $request->withAttribute(self::request_result_attribute, Result::authorized($user))
                    ->withAttribute(self::request_user_attribute, $user);
            }
        }

        return $request->withAttribute(self::request_result_attribute, Result::unauthorized());
    }

    /**
     * @param UserInterface $user
     * @return string
     */
    protected function getSID(UserInterface $user): string
    {
        if ($user instanceof SessionAwareInterface)
        {
            return $user->sessions()->current();
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

            $id = $this->getSID($user);

            if (!$this->viaRemember($request))
            {
                return FigResponseCookies::set($response, $this->setCookie($id));
            }

            return FigResponseCookies::set($response, $this->setCookie($id)->rememberForever());
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
        return ($user = $request->getAttribute(self::request_user_attribute)) instanceof UserInterface ? $user : null;
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