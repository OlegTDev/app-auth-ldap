<?php

declare(strict_types=1);

use App\Application\Services\JwtService;
use App\Application\Services\LdapService;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        JwtService::class => fn(ContainerInterface $c) => new JwtService(env('APP_URL'), env('JWT_SECRET'), env('LIFESPAN', 60)),
        LdapService::class => fn(ContainerInterface $c) => new LdapService(
            connectionString: env('LDAP_CONNECTION_STRING'),
            bindDn: env('LDAP_BIND_DN'),
            bindPassword: env('LDAP_BIND_PASSWORD'),
            findDn: env('LDAP_BASE_DN'),
        ),
    ]);
};
