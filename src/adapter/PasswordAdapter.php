<?php


namespace Bermuda\Authentication\Adapter;


use Bermuda\Authentication\Result;
use Bermuda\Authentication\UserProviderInterface;
use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Class PasswordAdapter
 * @package Bermuda\Authentication\Adapter
 */
class PasswordAdapter extends CookieAdapter
{
    private \Closure $validator;
    private \Closure $verificationCallback;
   
    private string $path;
    private string $identity;
    private string $credential;
    private string $rememberField;
         
    const FAILURE_VALIDATION = -1;
    const FAILURE_INVALID_CREDENTIAL = -2;
    const FAILURE_IDENTITY_NOT_FOUND = -3;
    
    const CONFIG_IDENTITY_KEY = 'identity';
    const CONFIG_CREDENTIAL_KEY = 'credential';
    const CONFIG_PATH_KEY = 'path';
    const CONFIG_REMEMBER_KEY = 'remember';
    const CONFIG_VALIDATOR_KEY = 'validator';
    const CONFIG_VERIFICATION_CALLBACK_KEY = 'verification_callback';

    public function __construct(UserProviderInterface $provider,
        callable $responseGenerator, array $config = [],
        SessionRepositoryInterface $repository = null
    )
    {
        parent::__construct($provider, $responseGenerator, $config[self::CONFIG_COOKIE_KEY] ?? [], $repository);
        
        $this->identity($config[self::CONFIG_IDENTITY_KEY] ?? 'email');
        $this->credential($config[self::CONFIG_CREDENTIAL_KEY] ?? 'pswd');
        $this->path($config[self::CONFIG_PATH_KEY] ?? '/login');
        $this->rememberField = $config[self::CONFIG_REMEMBER_KEY] ?? 'remember';
        
        array_key_exists(self::CONFIG_VERIFICATION_CALLBACK_KEY, $config) ? $this->setVerificationCallback($config[self::CONFIG_VERIFICATION_CALLBACK_KEY])
            :  $this->verificationCallback = static function(string $pswd, string $hash): bool
        {
           return password_verify($pswd, $hash);
        };
        
        array_key_exists(self::CONFIG_VALIDATOR_KEY, $config) ? $this->setValidator($config[self::CONFIG_VALIDATOR_KEY])
            :  $this->validator = function(array $input): array
        {
            $errors = [];
            
            if (!array_key_exists($this->identity, $input))
            {
                $errors[$this->identity] = 'Identity is required';
            }
            
            if (!array_key_exists($this->credential, $input))
            {
                $errors[$this->credential] = 'Credential is required';
            }
            
            return $errors; 
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
     * @param callable $validator
     * @return $this
     */
    public function setValidator(callable $validator): self
    {
        $this->validator = static function (array $input) use ($validator): array
        {
            return $validator($input);
        };
        
        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    protected function authenticateRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        if (strcasecmp($request->getMethod(), RequestMethodInterface::METHOD_POST) == 0
            && $this->path == $request->getUri()->getPath())
        {
            $params = (array) $request->getParsedBody();

            if (($messages = ($this->validator)($params)) == [])
            {
                if (null != ($user = $this->provider->provide($params[$this->identity])))
                {
                    if (($this->verificationCallback)($params[$this->credential], $user->getCredential()))
                    {
                        return $this->authenticated($request, $user, $remember);
                    }

                    $request->withAttribute(self::request_result_at, new Result(self::FAILURE_INVALID_CREDENTIAL, $this->getMessage(self::FAILURE_INVALID_CREDENTIAL)));
                }

                return $request->withAttribute(self::request_result_at, new Result(self::FAILURE_IDENTITY_NOT_FOUND, $this->getMessage(self::FAILURE_IDENTITY_NOT_FOUND)));
            }

            return $request->withAttribute(self::request_result_at, new Result(self::FAILURE_VALIDATION, $messages));
        }

        return parent::authenticateRequest($request);
    }
    
    /**
     * @param int $code
     * @return string
     */
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
