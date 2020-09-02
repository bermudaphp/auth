<?php


namespace App\Authentication;


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
     * @param UserInterface $user
     * @return static
     */
    public static function authorized(UserInterface $user): self
    {
        return new self(self::AUTHORIZED, 'AUTHORIZED', $user);
    }

    /**
     * @return static
     */
    public static function unauthorized(): self
    {
        return new self(self::UNAUTHORIZED, 'UNAUTHORIZED');
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