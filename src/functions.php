<?php

namespace Bermuda\Authentication;

use Psr\Http\Message\ServerRequestInterface;

/**
 * @param ServerRequestInterface $request
 * @return bool
 */
function hasUser(ServerRequestInterface $req): bool
{
    return getUser($req) != null;
}

/**
 * @param ServerRequestInterface $request
 * @return bool
 */
function getUserFromRequest(ServerRequestInterface $req):? UserInterface
{
    $result = $req->getAttribute(AdapterInterface::resultAt);

    if ($result instanceof Result && $result->getUser() != null)
    {
        return $result->getUser();
    }

    $user = $req->getAttribute(AdapterInterface::userAt);

    return $user instanceof UserInterface ? $user : null;
}
