<?php

namespace Bermuda\Authentication;

use function Bermuda\urlFor;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Bermuda\Authentication\Adapter\PasswordAdapter;
use Bermuda\Authentication\Adapter\CookieAdapter;

final class AdapterFactory
{    
    public function __invoke(ContainerInterface $container): CookieAdapter
    {        
        return new CookieAdapter($container->get(AdapterInterface::CONFIG_ID));
    }
}
