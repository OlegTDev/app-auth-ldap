<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\Action;
use App\Application\Services\JwtService;
use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class LdapLoginAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private readonly JwtService $jwtService,
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $domain = $this->request->getAttribute('domain_name');
        $username = $this->request->getAttribute('domain_username');
        $returnUrl = $this->request->getAttribute('return_url');

        $this->logger->info("Авторизация ldap. Пользователь: $username (), домен: $domain");
        $user = $this->userRepository->findBySamaccountName($username);

        $token = $this->jwtService->generateToken($username, ['domain' => $domain, ...$user->jsonSerialize()]);
        $redirectTo = concatenateUriWithJwt($returnUrl, $token);

        $this->logger->info("Переадресация на $redirectTo");

        return $this->response->withHeader('Location', $redirectTo)->withStatus(302);
    }
}
