<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\Action;
use App\Application\Services\JwtService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class BasicLoginAction extends Action
{
    public function __construct(LoggerInterface $logger, private readonly JwtService $jwtService)
    {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $domain = $this->request->getAttribute('domain_name');
        $username = $this->request->getAttribute('domain_username');
        $returnUrl = $this->request->getAttribute('return_url');

        $this->logger->info("Авторизация basic. Пользователь: $username, домен: $domain");

        $token = $this->jwtService->generateToken($username, ['domain' => $domain, 'username' => $username]);
        $redirectTo = concatenateUriWithJwt($returnUrl, $token);

        $this->logger->info("Переадресация на $redirectTo");

        return $this->response->withHeader('Location', $redirectTo)->withStatus(302);
    }
}
