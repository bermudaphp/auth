<?php


namespace Bermuda\Authentication;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Bermuda\Authentication\Adapter\CookieAdapter;
use Bermuda\Authentication\Adapter\PasswordAdapter;


use function Bermuda\redirect_on_route;


class AdapterFactory
{
    public function __invoke(ContainerInterface $container)
    {
    
        return [
            'dependencies' => [
                'factories' => [
                    AdapterInterface::class => static function(ContainerInterface $c): AdapterInterface
                    {
                        $responseGenerator = static function(): ResponseInterface
                        {
                            return redirect_on_route('login');
                        };
                        
                        $provider = $c->get(UserProviderInterface::class);
                        
                       

                        return new CookieAdapter($provider, $responseGenerator, $c->get('config')['auth.config'] ?? [], $delegate);
                    }
                ]
            ]
        ];
    }
}
