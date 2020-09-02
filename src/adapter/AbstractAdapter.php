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


    /**
     * @var string[]
     */
    protected array $messages = [
        Result::FAILURE => 'Authentication failed'
    ];

    protected const request_remember_at = 'request_remember_at';

    public function __construct(UserProviderInterface $provider, callable $responseGenerator)
    {
        $this->provider = $provider;
        $this->responseGenerator = static function (ServerRequestInterface $req) use ($responseGenerator): ResponseInterface
        {
            return $responseGenerator($req);
        };
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
            return Result::authorized($request)->withAttribute(self::request_remember_at, $remember);
        }

        return $this->authenticateRequest($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    abstract protected function authenticateRequest(ServerRequestInterface $request): ServerRequestInterface ;

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
}
