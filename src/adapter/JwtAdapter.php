<?php


namespace App;


use Bermuda\Http\Response;
use Bermuda\Authentication\Result;
use Psr\Http\Message\ResponseInterface;
use Bermuda\Authentication\UserInterface;
use Psr\Http\Message\ServerRequestInterface;
use Bermuda\Authentication\UserProviderInterface;
use Bermuda\Authentication\Adapter\AbstractAdapter;
use Bermuda\Authentication\SessionRepositoryInterface;


/**
 * Class JwtAdapter
 * @package App
 */
class JwtAdapter extends AbstractAdapter
{
    private TokenGeneratorInterface $generator;
    private TokenValidatorInterface $validator;
    private RefreshTokenStorage $storage;

    public const request_access_token_at = 'access_token';
    public const request_refresh_token_at = 'refresh_token';

    public function __construct(UserProviderInterface $provider, callable $responseGenerator,
        TokenGeneratorInterface $generator, TokenValidatorInterface $validator,
        RefreshTokenStorage $storage, ?SessionRepositoryInterface $repository = null
    )
    {
        parent::__construct($provider, $responseGenerator, $repository);
        $this->generator = $generator;
        $this->validator = $validator;
        $this->storage   = $storage;
    }

    /**
     * @inheritDoc
     */
    public function authenticate(ServerRequestInterface $request, UserInterface $user = null, bool $remember = false): ServerRequestInterface
    {
        if (($token = $this->getAccessTokenFromRequest($request)) != null)
        {
            if ($this->validator->validate($token, $data))
            {
                return Result::authorized($request,
                    $this->provider->provide(
                        $this->getIdentityFromParsedToken($data)
                    ), $remember
                );
            }

            $refreshToken = $this->getRefreshTokenFromRequest($request);

            if ($refreshToken != null && $this->storage->hasToken($refreshToken))
            {
                $user = $this->provider->provide(
                    $this->storage->getUserIdentityFromRefreshToken($refreshToken)
                );

                $request = $this->writeRefreshToken(
                    $this->writeAccessToken($request)
                );

                return Result::authorized($request, $user, $remember);
            }

            new Result();
        }

        return Result::unauthorized($request);
    }

    /**
     * @inheritDoc
     */
    public function unauthorized(ServerRequestInterface $request): ResponseInterface
    {
        return Response::make();
    }

    /**
     * @inheritDoc
     */
    public function clear(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    /**
     * @inheritDoc
     */
    public function write(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    /**
     * @param array $data
     * @return string
     */
    private function getIdentityFromParsedToken(array $data): string
    {
        return $data['uid'];
    }

    /**
     * @param ServerRequestInterface $request
     * @return string
     */
    private function getAccessTokenFromRequest(ServerRequestInterface $request):? string
    {
        return null;
    }

    /**
     * @param ServerRequestInterface $request
     * @return string|null
     */
    private function getRefreshTokenFromRequest(ServerRequestInterface $request):? string
    {
        return null;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    private function writeAccessToken(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request->withAttribute(self::request_access_token_at,
            $this->generator->generateAccessToken()
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    private function writeRefreshToken(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request->withAttribute(self::request_refresh_token_at,
            $this->generator->generateRefreshToken($accessToken = $request->getAttribute(self::request_access_token_at))
        );
    }
}
