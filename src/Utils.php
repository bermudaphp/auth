<?php

namespace Bermuda\Authentication;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Utils
 * @package Bermuda\Authentication
 */
final class Utils
{
    private function __construct()
    {}

    /**
     * @param ServerRequestInterface $request
     * @return UserInterface|null
     */
    public static function user(ServerRequestInterface $request):? UserInterface
    {
        $result = $request->getAttribute(AdapterInterface::resultAt);

        if ($result instanceof Result && $result->getUser() != null)
        {
            return $result->getUser();
        }

        $user = $request->getAttribute(AdapterInterface::userAt);

        return $user instanceof UserInterface ? $user : null;
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    public static function hasUser(ServerRequestInterface $request): bool
    {
        return self::user($request) != null;
    }
}