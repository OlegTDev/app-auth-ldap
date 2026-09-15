<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\Action;
use App\Application\Services\JwtService;
use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class LoginAction extends Action
{
    public function __construct(LoggerInterface $logger, private JwtService $jwtService, private UserRepository $userRepository) 
    {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $userAttribute = env('DOMAIN_USER_ATTR_LOGIN', 'REMOTE_USER');
        $domainUser = $this->request->getServerParams()[$userAttribute] ?? null;        
        if (!$domainUser) {
            $this->logger->error("Доменная авторизация не сработала. Не передан логин, переменная $userAttribute = NULL.");
            $this->response->getBody()->write('Доменная авторизация не сработала.');
            return $this->response->withStatus(401);
        }
        $this->logger->info("Авторизация $domainUser");

        $returnUrl = $this->request->getQueryParams()['return_url'] ?? null;
        if (!$returnUrl) {
            $this->logger->error('Не передан параметр return_url');
            $this->response->getBody()->write('Не передан параметр return_url');
            return $this->response->withStatus(401);
        }

        [$domain, $username] = $this->parseUsername($domainUser);

        $user = $this->userRepository->findBySamaccountName($username);
                
        $token = $this->jwtService->generateToken($username, ['domain' => $domain, ...$user->jsonSerialize()]);
        $redirectTo = $this->concatenateUri($returnUrl, $token);
        return $this->response->withHeader('Location', $redirectTo)->withStatus(302);
    }

    private function parseUsername(string $domainUser): array
    {
        $parts = \explode('\\', $domainUser);
        return [
            \count($parts) > 1 ? $parts[0] : '',
            \count($parts) > 1 ? $parts[1] : $parts[0], 
        ];
    }

    private function concatenateUri(string $returnUrl, string $jwtToken): string
    {
        $separator = (parse_url($returnUrl, PHP_URL_QUERY) == NULL) ? '?' : '&';
        return "{$returnUrl}{$separator}token=$jwtToken";
    }
}