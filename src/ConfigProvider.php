<?php


namespace App\Authentication;


use App\Authentication\Adapter\CookieAdapter;
use App\Entity\User;
use Cycle\ORM\ORMInterface;
use App\Authentication\Adapter\PasswordAdapter;
use App\Authentication\provider\UserProvider;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;


use function Bermuda\on;


class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                'factories' => [
                    UserProviderInterface::class => static function(ContainerInterface $container): UserProviderInterface
                    {
                        return $container->get(ORMInterface::class)->getRepository(User::class);
                    },

                    AdapterInterface::class => function(ContainerInterface $c): AdapterInterface
                    {
                        $responseGenerator = static function(): ResponseInterface
                        {
                            return on('login');
                        };

                        return new PasswordAdapter($c->get(UserProviderInterface::class), $responseGenerator, $c->get('config')['auth.config'] ?? []);
                    }
                ]
            ]
        ];
    }
}