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
final class PasswordAdapter extends AbstractAdapter
{
    private \Closure $validator;
    private \Closure $verificationCallback;
   
    private string $identity;
    private string $credential;
    private string $rememberField;
         
    const FAILURE_VALIDATION = -1;
    const FAILURE_INVALID_CREDENTIAL = -2;
    const FAILURE_IDENTITY_NOT_FOUND = -3;
    
    const CONFIG_IDENTITY_KEY = 'PasswordAdapter:identity';
    const CONFIG_CREDENTIAL_KEY = 'PasswordAdapter:credential';
    const CONFIG_REMEMBER_KEY = 'PasswordAdapter:remember';
    const CONFIG_VALIDATOR_KEY = 'PasswordAdapter:validator';
    const CONFIG_VERIFICATION_CALLBACK_KEY = 'PasswordAdapter:verificationCallback';

    public function __construct(array $config)
    {
        parent::__construct($config);
        
        $this->identity($config[self::CONFIG_IDENTITY_KEY] ?? 'email');
        $this->credential($config[self::CONFIG_CREDENTIAL_KEY] ?? 'pswd');
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
        $params = $this->getParamsFromRequest($request);
        
        if (($messages = ($this->validator)($params)) == [])
        {
            if (null != ($user = $this->provider->provide($params[$this->identity])))
            {
                if (($this->verificationCallback)($params[$this->credential], $user->getCredential()))
                {
                    return $this->forceAuthentication($request, $user, $this->viaRemember($request));
                }
                
                $request->withAttribute(self::request_result_at, new Result(self::FAILURE_INVALID_CREDENTIAL, $this->getMessage(self::FAILURE_INVALID_CREDENTIAL)));
            }

            return $request->withAttribute(self::request_result_at, new Result(self::FAILURE_IDENTITY_NOT_FOUND, $this->getMessage(self::FAILURE_IDENTITY_NOT_FOUND)));
        }

        return $request->withAttribute(self::request_result_at, new Result(self::FAILURE_VALIDATION, $messages));
    }
    
    private function getParamsFromRequest(ServerRequestInterface $request): array
    {
        if (!empty($params = $request->getParsedBody()))
        {
            return (array) $params;
        }
        
        return (array) $request->getQueryParams();
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
        return ($request->getParsedBody()[$this->rememberField] ?? '' == 'on');
    }
}
