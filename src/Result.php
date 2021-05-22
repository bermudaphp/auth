<?php

namespace Bermuda\Authentication;

use Psr\Http\Message\ServerRequestInterface;

final class Result
{
    const AUTHORIZED = 1;
    const UNAUTHORIZED = 2;
    const FAILURE = 0;
    const CREDENTIAL_IS_MISSING = -1;
    const CREDENTIAL_IS_INVALID = -2;
    const IDENTITY_IS_MISSING = -3;
    const IDENTITY_NOT_FOUND = -4;
    
    private int $code;
    private ?array $messages = null;
    private ?UserInterface $user = null;

    private function __construct(int $code = self::FAILURE, ?UserInterface $user = null, array $messages = null)
    {
        $this->messages = $messages; $this->code = $code; $this->user = $user;
    }

    /**
     * @param ServerRequestInterface $user
     * @param UserInterface $user
     * @return ServerRequestInterface
     */
    public static function authorized(UserInterface $user): self
    {
        return new self(self::AUTHORIZED, $user);
    }

    public static function unauthorized(): self
    {
        return new self(self::UNAUTHORIZED);
    }
    
    public static function failure($messages, int $code = self::FAILURE): self
    {
        return new self($code, null, is_array($messages) ? $messages : [$messages]);
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
        return $this->code <= self::FAILURE;
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
    public function getMessages(): array
    {
        return $this->messages;
    }
}
