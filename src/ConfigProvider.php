<?php


namespace Bermuda\Authentication;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Bermuda\Authentication\Adapter\CookieAdapter;
use Bermuda\Authentication\Adapter\PasswordAdapter;


use function Bermuda\redirect_on_route;


final class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                'factories' => [
                    AdapterInterface::class => AdapterFactory::class
                ]
            ]
        ];
    }
}
