<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;

class WindowsAuthenticationMiddleware implements Middleware
{

    public function __construct(private LoggerInterface $logger, private ResponseFactoryInterface $response)
    {}

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $userAttribute = env('DOMAIN_USER_ATTR_LOGIN', 'REMOTE_USER');
        $serverParams = $request->getServerParams();
        $domainUser = $serverParams[$userAttribute] ?? null;

        if (!$domainUser) {
            $clientIp = $request->getAttribute('ip_address');
            
            $this->logger->error("Доменная авторизация не сработала. Переменная $userAttribute не передана или пустая. IP: $clientIp");
            $response = $this->response->createResponse(401);
            $response->getBody()->write('Доменная авторизация не сработала.');
            return $response;
        }

        [$domain, $username] = $this->parseUsername($domainUser);

        $requestWithAttributes = $request
            ->withAttribute('domain_name', $domain)
            ->withAttribute('domain_username', $username);

        return $handler->handle($requestWithAttributes);
    }

    private function parseUsername(string $domainUser): array
    {
        $parts = \explode('\\', $domainUser);
        return [
            \count($parts) > 1 ? $parts[0] : '',
            \count($parts) > 1 ? $parts[1] : $parts[0], 
        ];
    }
}