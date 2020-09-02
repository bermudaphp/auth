<?php


namespace App\Authentication\Adapter;


use App\Authentication\Result;
use App\Authentication\UserProviderInterface;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Class PasswordAdapter
 * @package App\Auth\Adapter
 */
class PasswordAdapter extends CookieAdapter
{
    private \Closure $verificationCallback;

    private ?string $identity = null;
    private ?string $credential = null;
    private string $rememberField = '';

    protected array $messages = [
        Result::FAILURE => 'Incorrect email or password! Try again.',
        self::FAILURE_IDENTITY_NOT_FOUND => '',
        self::FAILURE_VALIDATION => '',
        self::FAILURE_INVALID_CREDENTIAL => '',
    ];

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
        if (strtoupper($request->getMethod()) != 'POST')
        {
            return parent::authenticateRequest($request);
        }

        $params = (array) $request->getParsedBody();

        if (array_key_exists($this->identity, $params)
            && array_key_exists($this->credential, $params))
        {
            if (null != ($user = $this->provider->provide($params[$this->identity])))
            {
                if(($this->verificationCallback)($params[$this->credential], $user->getCredential()))
                {
                    return $request->withAttribute(self::request_result_attribute, Result::authorized($user))
                        ->withAttribute(self::request_user_attribute, $user);
                }

                $req->withAttribute(self::request_result_attribute, new Result(self::FAILURE_INVALID_CREDENTIAL, $this->messages[self::FAILURE_INVALID_CREDENTIAL]));
            }

            return $request->withAttribute(self::request_result_attribute, new Result(self::FAILURE_IDENTITY_NOT_FOUND, $this->messages[self::FAILURE_IDENTITY_NOT_FOUND]));
        }

        return $request->withAttribute(self::request_result_attribute, new Result(self::FAILURE_VALIDATION, $this->messages[self::FAILURE_VALIDATION]));
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