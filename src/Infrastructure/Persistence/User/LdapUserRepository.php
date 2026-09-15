<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Application\Services\LdapService;
use App\Domain\User\User;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use Psr\Log\LoggerInterface;

class LdapUserRepository implements UserRepository
{
    public function __construct(private LdapService $ldapService, private LoggerInterface $logger)
    {}

    public function findBySamaccountName(string $samaccountName): User
    {
        $userData = $this->ldapService->query($samaccountName);
        
        if (empty($userData)) {
            $this->logger->error("Пользователь $samaccountName не найден на сервере LDAP");
            throw new UserNotFoundException();
        }

        return new User(
            sAMAccountName: $samaccountName,
            cn: $userData['cn'],
            userPrincipalName: $userData['userPrincipalName'],
            sn: $userData['sn'],
            displayName: $userData['displayName'],
            mail: $userData['mail'],
            memberOf: $userData['memberOf'],
            company: $userData['company'],
            department: $userData['department'],
            title: $userData['title'],
            physicalDeliveryOfficeName: $userData['physicalDeliveryOfficeName'],
            telephoneNumber: $userData['telephoneNumber'],
            streetAddress: $userData['streetAddress'],
            postalCode: $userData['postalCode'],
            st: $userData['st'],
            ipPhone: $userData['ipPhone'],
            countryCode: $userData['countryCode'],
            lastLogon: $userData['lastLogon'],
        );
    }

}
