<?php

declare(strict_types=1);

use App\Application\Actions\Auth\LoginAction;
use Slim\App;

return function (App $app) {
    $app->get('/', LoginAction::class);
};
