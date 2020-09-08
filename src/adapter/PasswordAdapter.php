<?php


namespace Bermuda\Authentication\Adapter;


use Bermuda\Authentication\Result;
use Bermuda\Authentication\UserProviderInterface;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Class PasswordAdapter
 * @package Bermuda\Authentication\Adapter
 */
class PasswordAdapter extends CookieAdapter
{
    private \Closure $verificationCallback;

    private strin $path;
    private string $identity;
    private string $credential;
    private string $rememberField;
        
    const FAILURE_VALIDATION = -1;
    const FAILURE_INVALID_CREDENTIAL = -2;
    const FAILURE_IDENTITY_NOT_FOUND = -3;

    public function __construct(UserProviderInterface $provider,
        callable $responseGenerator, array $config = []
    )
    {
        parent::__construct($provider, $responseGenerator, $config['cookie'] ?? []);

        $this->identity($config['identity'] ?? 'email');
        $this->credential($config['credential'] ?? 'pswd');
        $this->path($config['path'] ?? '/login');

        $this->rememberField = $config['remember'] ?? 'remember';
        

        $this->verificationCallback = static function(string $pswd, string $hash): bool
        {
           return password_verify($pswd, $hash);
        };
    }

    /**
     * @param string|null $identity
     * @return string
     */
    public function identity(string $identity = null): string
    {
        if ($identity != null)
        {
            $this->identity = $identity;
        }

        return $this->identity;
    }
    
    /**
     * @param string|null $path
     * @return string
     */
    public function path(string $path = null): string
    {
        if ($path != null)
        {
            $this->path = $path;
        }

        return $this->path;
    }


    /**
     * @param string|null $credential
     * @return string
     */
    public function credential(string $credential = null): string
    {
        if ($credential != null)
        {
            $this->credential = $credential;
        }

        return $this->credential;
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function setVerificationCallback(callable $callback): self
    {
        $this->verificationCallback = static function (string $pswd, string $hash) use ($callback): bool
        {
            return (bool) $callback($pswd, $hash);
        };

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    protected function authenticateRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        if (strcasecmp($request->getMethod(), RequestMethodInterface::METHOD_POST) 
            && $this->path == $request->getUri()->getPath())
        {
            $params = (array) $request->getParsedBody();

            if (array_key_exists($this->identity, $params)
                && array_key_exists($this->credential, $params))
            {
                if (null != ($user = $this->provider->provide($params[$this->identity])))
                {
                    if (($this->verificationCallback)($params[$this->credential], $user->getCredential()))
                    {
                        return Result::authorized($request, $user);
                    }

                    $request->withAttribute(self::request_result_at, new Result(self::FAILURE_INVALID_CREDENTIAL, $this->getMessage(self::FAILURE_INVALID_CREDENTIAL)));
                }

                return $request->withAttribute(self::request_result_at, new Result(self::FAILURE_IDENTITY_NOT_FOUND, $this->getMessage(self::FAILURE_IDENTITY_NOT_FOUND)));
            }

            return $request->withAttribute(self::request_result_at, new Result(self::FAILURE_VALIDATION, $this->getMessage(self::FAILURE_VALIDATION)));
        }

        return parent::authenticateRequest($request);
    }
    
    protected function getMessage(int $code): string
    {
        return $this->messages[$code] ?? sprintf('Incorrect %s or password! Try again.', $this->identity);
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function viaRemember(ServerRequestInterface $request): bool
    {
        return ($request->getParsedBody()[$this->rememberField] ?? null == 'on') || parent::viaRemember($request);
    }
}
