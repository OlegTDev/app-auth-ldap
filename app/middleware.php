<?php

declare(strict_types=1);

use RKA\Middleware\IpAddress;
use Slim\App;
use App\Application\Middleware\ReturnUrlMiddleware;
use App\Application\Middleware\WindowsAuthenticationMiddleware;

return function (App $app) {
    $app->addBodyParsingMiddleware();

    $app->add(ReturnUrlMiddleware::class);
    $app->add(WindowsAuthenticationMiddleware::class);
    $app->add(new IpAddress(true, []));

    $app->addRoutingMiddleware();

    $prod = env('PROD', false);
    $app->addErrorMiddleware(!$prod, true, true);
};
