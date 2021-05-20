<?php

namespace Bermuda\Authentication;

use function Bermuda\view;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

final class AdapterFactory
{    
    public function __invoke(ContainerInterface $container): Adapter\CookieAdapter
    {        
        $config = $container->get('config')[AdapterInterface::container_config_id];
        $config[Adapter\AbstractAdapter::CONFIG_USER_PROVIDER_KEY] = $container->get(UserProviderInterface::class);
        isset($config[Adapter\AbstractAdapter::CONFIG_RESPONSE_GENERATOR_KEY]) ?:
            $config[Adapter\AbstractAdapter::CONFIG_RESPONSE_GENERATOR_KEY] = static fn(): ResponseInterface => view('app::login');
        
        return new Adapter\PasswordAdapter($config);
    }
}
