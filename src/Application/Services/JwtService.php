<?php

declare(strict_types=1);

namespace App\Application\Services;

use Firebase\JWT\JWT;

class JwtService
{
    public function __construct(private string $appUrl, private string $secretKey, private int $lifespan)
    {}

    public function generateToken(string $username, array $data): string
    {
        $payload = [
            'iss' => $this->appUrl,
            'sub' => $username,
            'iat' => time(),
            'exp' => time() + $this->lifespan,
            'jti' => bin2hex(random_bytes(8)),
            ...$data,
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }
    
}