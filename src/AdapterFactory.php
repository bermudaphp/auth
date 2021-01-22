<?php

namespace Bermuda\Authentication;

use function Bermuda\urlFor;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

final class AdapterFactory
{    
    public function __invoke(ContainerInterface $container): CookieAdapter
    {        
        $config = $container->get('config')[AdapterInterface::CONFIG_ID]->toArray();
        $config[Adapter\AbstractAdapter::CONFIG_USER_PROVIDER_KEY] = $container->get(UserProviderInterface::class);
        
        return new Adapter\CookieAdapter($config);
    }
}
