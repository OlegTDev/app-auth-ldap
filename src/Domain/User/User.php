<?php

declare(strict_types=1);

namespace App\Domain\User;

use JsonSerializable;

class User implements JsonSerializable
{
    public function __construct(
        private string $sAMAccountName,
        private string $cn,
        private ?string $userPrincipalName = null,
        private ?string $givenName = null,
        private ?string $sn = null,
        private ?string $displayName = null,
        private ?string $mail = null,
        private array $memberOf = [],
        private ?string $company = null,
        private ?string $department = null,
        private ?string $title = null,
        private ?string $physicalDeliveryOfficeName = null,
        private ?string $telephoneNumber = null,
        private ?string $streetAddress = null,
        private ?string $postalCode = null,
        private ?string $st = null,
        private ?string $ipPhone = null,
        private ?string $countryCode = null,
        private ?string $lastLogon = null,
    ) {}       

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'sAMAccountName' => $this->sAMAccountName,
            'cn' => $this->cn,
            'userPrincipalName' => $this->userPrincipalName,
            'givenName' => $this->givenName,
            'sn' => $this->sn,
            'displayName' => $this->displayName,
            'mail' => $this->mail,
            'memberOf' => $this->memberOf,
            'company' => $this->company,
            'department' => $this->department,
            'title' => $this->title,
            'physicalDeliveryOfficeName' => $this->physicalDeliveryOfficeName,
            'telephoneNumber' => $this->telephoneNumber,
            'streetAddress' => $this->streetAddress,
            'postalCode' => $this->postalCode,
            'st' => $this->st,
            'ipPhone' => $this->ipPhone,
            'countryCode' => $this->countryCode,
            'lastLogon' => $this->lastLogon,
        ];
    }
}
