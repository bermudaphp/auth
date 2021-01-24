<?php

namespace Bermuda\Authentication;

use function Bermuda\urlFor;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

final class AdapterFactory
{    
    public function __invoke(ContainerInterface $container): Adapter\CookieAdapter
    {        
        $config = $container->get('config')[AdapterInterface::CONFIG_ID];
        $config[Adapter\AbstractAdapter::CONFIG_USER_PROVIDER_KEY] = $container->get(UserProviderInterface::class);

        if (!isset($config[Adapter\AbstractAdapter::CONFIG_RESPONSE_GENERATOR_KEY]))
        {
            $config[Adapter\AbstractAdapter::CONFIG_RESPONSE_GENERATOR_KEY] = static function()
            {
                return urlFor('signIn');
            };
        }
        
        return new Adapter\CookieAdapter($config);
    }
}
