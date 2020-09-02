<?php


namespace Bermuda\Authentication;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


/**
 * Class SessionMiddleware
 * @package Bermuda\Authentication
 */
class SessionMiddleware implements MiddlewareInterface
{
    /**
     * @var callable
     */
    private $datetimeFactory;
    private SessionRepositoryInterface $repository;

    public function __construct(SessionRepositoryInterface $repository, callable $datetimeFactory = null)
    {
        $this->repository = $repository;
        $this->datetimeFactory = static function() use ($datetimeFactory): \DateTimeInterface
        {
            return $datetimeFactory() ?? new \DateTimeImmutable();
        };
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute(AdapterInterface::request_user_attribute);

        if ($user instanceof SessionAwareInterface)
        {
            ($session = $user->sessions()->current())->activity(($this->datetimeFactory)());
            $this->repository->store($session);
        }

        return $handler->handle($request);
    }
}
