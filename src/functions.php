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
function getUser(ServerRequestInterface $req):? UserInterface
{
    return $req->getAttribute(AdapterInterface::userAt, null);
}
