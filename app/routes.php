<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {        
        $domainUser = $_SERVER['REMOTE_USER'] ?? null; 
		/*if (!$domainUser) {
			return $response
				->withStatus(401)
				->withHeader('WWW-Authenticate', 'Negotiate')
				//->withHeader('WWW-Authenticate', 'NTLM');
		}
		*/
		
        if (!$domainUser) {
            $response->getBody()->write("Доменная авторизация не сработала");
            return $response->withStatus(401);
        }
		

        $username = strtolower(basename(str_replace('\\', '/', $domainUser)));

        // 2. Создаем одноразовый JWT
        $secretKey = 'ваш_секретный_ключ_совместный_с_laravel';
        $payload = [
            'iss' => 'https://iis.local',    // Кто выпустил
            'sub' => $username,                   // Имя пользователя
            'iat' => time(),                      // Время выпуска
            'exp' => time() + 60,                 // Протухнет через 60 секунд
            'jti' => bin2hex(random_bytes(8))    // Уникальный ID токена (от повторного использования)
        ];

        $jwt = JWT::encode($payload, $secretKey, 'HS256');

        // 3. Возвращаем юзера в Laravel, передав токен в URL
        $returnUrl = $request->getQueryParams()['return_url'] ?? 'https://laravel-service.local';
        
        // Склеиваем URL с токеном
        $separator = (parse_url($returnUrl, PHP_URL_QUERY) == NULL) ? '?' : '&';
        $redirectUrl = $returnUrl . $separator . 'token=' . $jwt;        

        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    });
	
	$app->get('/ping', function (Request $request, Response $response) {
		$payload = json_encode(['status' => 'OKs']);
		$response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });
};
