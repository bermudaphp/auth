<?php


namespace Bermuda\Authentication;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Bermuda\Authentication\Adapter\PasswordAdapter;
use Bermuda\Authentication\Adapter\CookieAdapter;


use function Bermuda\redirect_on_route;


final class AdapterFactory
{
    public const container_config_id = 'auth.config';
    public const container_delegate_id = 'auth.delegate';
    public const container_response_generator_id = 'auth.response_generator';
    
    public function __invoke(ContainerInterface $container): CookieAdapter
    {
        $generator = $container->has(self::container_response_generator_id) ? $container->get(self::container_response_generator_id)
            : static function(): ResponseInterface { return redirect_on_route('login'); };
        
        $config = $container->has(self::container_config_id) ? $container->get(self::container_config_id) : [];
        $repository = $container->has(SessionRepositoryInterface::class) ? $container->get(SessionRepositoryInterface::class) : null;
        
        $delegate = $container->has(self::container_delegate_id) ? $container->get(self::container_delegate_id) 
            : new PasswordAdapter($container->get(UserProviderInterface::class), $generator, $config, $repository);
        
        return new CookieAdapter($delegate->provider(), $generator, $config, $repository, $delegate);
    }
}
