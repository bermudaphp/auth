<?php


namespace Bermuda\Authentication;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Bermuda\Authentication\Provider\UserProvider;
use Bermuda\Authentication\Adapter\CookieAdapter;
use Bermuda\Authentication\Adapter\PasswordAdapter;


use function Bermuda\redirect_on_route;


class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                'factories' => [
                    AdapterInterface::class => function(ContainerInterface $c): AdapterInterface
                    {
                        $responseGenerator = static function(): ResponseInterface
                        {
                            return redirect_on_route('login');
                        };

                        return new PasswordAdapter($c->get(UserProviderInterface::class), $responseGenerator, $c->get('config')['auth.config'] ?? []);
                    }
                ]
            ]
        ];
    }
}
