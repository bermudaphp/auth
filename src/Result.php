<?php


namespace Bermuda\Authentication;


use Psr\Http\Message\ServerRequestInterface;


final class Result
{
    const AUTHORIZED = 1;
    const UNAUTHORIZED = 2;
    const FAILURE = 0;

    private int $code;
    private array $msgs = [];
    private ?UserInterface $user = null;

    /**
     * @param int $code
     * @param string|array $msgs
     * @param UserInterface|null $user
     */
    public function __construct(int $code = self::FAILURE, $msgs = [], ?UserInterface $user = null)
    {
        $this->code = $code;
        $this->msgs  = (array) $msgs;
        $this->user = $user;
    }

    /**
     * @param ServerRequestInterface $user
     * @param UserInterface $user
     * @return ServerRequestInterface
     */
    public static function authorized(ServerRequestInterface $request, UserInterface $user, bool $remember = false): ServerRequestInterface
    {
        return $request->withAttribute(AdapterInterface::request_result_at, new self(self::AUTHORIZED, [], $user))
            ->withAttribute(AdapterInterface::request_user_at, $user)
            ->withAttribute(AdapterInterface::request_remember_at, $remember);
    }

    /**
     * @return ServerRequestInterface
     */
    public static function unauthorized(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request->withAttribute(AdapterInterface::request_result_at, new self(self::UNAUTHORIZED));
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
     * @return array
     */
    public function getMsgs(): array
    {
        return $this->msgs;
    }
}
