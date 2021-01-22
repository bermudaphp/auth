<?php

namespace Bermuda\Authentication;

use Psr\Http\Message\ServerRequestInterface;

/**
 * @param ServerRequestInterface $request
 * @return bool
 */
function hasUser(ServerRequestInterface $req): bool
{
    return $req->getAttribute(AdapterInterface::userAt) instanceof UserInterface ;
}
