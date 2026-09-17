<?php

declare(strict_types=1);

use App\Application\Actions\Auth\BasicLoginAction;
use App\Application\Actions\Auth\LdapLoginAction;
use App\Application\Middleware\ReturnUrlMiddleware;
use App\Application\Middleware\WindowsAuthenticationMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {    
    $app->group('/auth', function(RouteCollectorProxy $group) {
        $group->get('/basic', BasicLoginAction::class);
        $group->get('/ldap', LdapLoginAction::class);

    });
    // ->add(ReturnUrlMiddleware::class)
    // ->add(WindowsAuthenticationMiddleware::class);
};
