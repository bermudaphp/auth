<?php


namespace Bermuda\Authentication;


use Psr\Http\Message\ServerRequestInterface;


final class Result
{
    const AUTHORIZED = 1;
    const UNAUTHORIZED = 2;
    const FAILURE = 0;

    private int $code;
    private ?string $msg = null;
    private ?UserInterface $user = null;

    public function __construct(int $code = self::FAILURE, ?string $msg = null, ?UserInterface $user = null)
    {
        $this->code = $code;
        $this->msg  = $msg;
        $this->user = $user;
    }

    /**
     * @param ServerRequestInterface $user
     * @param UserInterface $user
     * @return ServerRequestInterface
     */
    public static function authorized(ServerRequestInterface $request, UserInterface $user): ServerRequestInterface
    {
        return $request->withAttribute(AdapterInterface::request_result_attribute, new self(self::AUTHORIZED, 'AUTHORIZED', $user))
            ->withAttribute(AdapterInterface::request_user_attribute, $user);
    }

    /**
     * @return ServerRequestInterface
     */
    public static function unauthorized(ServerRequestInterface $request): ServerRequestInterface
    {
        return return $request->withAttribute(AdapterInterface::request_result_attribute, new self(self::AUTHORIZED, 'AUTHORIZED', $user));
    }

    /**
     * @return bool
     */
    public function isAuthorized(): bool
    {
        return $this->code == self::AUTHORIZED;
    }

    /**
     * @return UserInterface|null
     */
    public function getUser():? UserInterface
    {
        return $this->user;
    }

    /**
     * @return bool
     */
    public function isFailure(): bool
    {
        return self::FAILURE >= $this->code;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string|null
     */
    public function getMsg():? string
    {
        return $this->msg;
    }
}
