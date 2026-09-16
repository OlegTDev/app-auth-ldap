<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;

class ReturnUrlMiddleware implements Middleware
{

    public function __construct(private LoggerInterface $logger, private ResponseFactoryInterface $response)
    {}

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $returnUrl = $request->getQueryParams()['return_url'] ?? null;
        if (!$returnUrl) {
            $this->logger->error('Не передан параметр return_url');
            $response = $this->response->createResponse(401);
            $response->getBody()->write('Не передан параметр return_url');
            return $response;
        }

        $requestWithAttributes = $request->withAttribute('return_url', $returnUrl);

        return $handler->handle($requestWithAttributes);
    }
}