<?php

namespace Bermuda\Authentication;

use function Bermuda\view;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

final class AdapterFactory
{    
    public const cookie_params_id = 'Bermuda\Authentication\AdapterFactory@cookie_params';
    public function __invoke(ContainerInterface $container): Adapter\PasswordAdapter
    {         
        ($adapter = new Adapter\PasswordAdapter($provider = $container->get(UserProviderInterface::class), $generator = static fn(): ResponseInterface => view('app::login')))
            ->setNext(new Adapter\CookieAdapter($provider, $generator, $container->get(self::cookie_params_id)));
        
        return $adapter;
    }
}
