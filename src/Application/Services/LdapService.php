<?php

declare(strict_types=1);

namespace App\Application\Services;

use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Ldap;

class LdapService
{
    public function __construct(
        private string $connectionString,
        private string $bindDn,
        private string $bindPassword,
        private string $findDn,
    ) {}

    /**
     * @param string $samaccountName
     * @return array{
     *      sAMAccountName: string, cn: string, userPrincipalName?: string, givenName?: string, sn?: string,
     *      displayName?: string, mail?: string, company?: string, department?: string, title?: string,
     *      physicalDeliveryOfficeName?: string, telephoneNumber?: string, streetAddress?: string,
     *      postalCode?: string, st?: string, ipPhone?: string, countryCode?: string,
     *      memberOf?: string[], lastLogon: string,
     * }|null
     */
    public function query(string $samaccountName): array|null
    {
        $ldap = Ldap::create('ext_ldap', ['connection_string' => $this->connectionString, 'encryption' => 'none']);        
        $ldap->bind($this->bindDn, $this->bindPassword);
        
        $query = $ldap->query($this->findDn, "(sAMAccountName=$samaccountName)", ['scope' => QueryInterface::SCOPE_ONE]);
        $result = $query->execute();
        
        if ($result->count() === 0) {
            return null;
        }        
        
        $entity = $result->toArray()[0];
        
        $data['sAMAccountName'] = $samaccountName;
        
        $stringAttributes = [
            'cn', 'userPrincipalName', 'givenName', 'sn', 'displayName', 'mail', 'company', 'department',
            'title', 'physicalDeliveryOfficeName', 'telephoneNumber', 'streetAddress', 'postalCode',
            'st', 'ipPhone', 'countryCode',
        ];
        foreach ($stringAttributes as $attribute) {
            $data[$attribute] = $this->getFirstAttribute($entity, $attribute);
        }

        $data['memberOf'] = $this->parseMemberOf($entity->getAttribute('memberOf'));
        $data['lastLogon'] = $this->convertAdTimestamp($entity->getAttribute('lastLogon')[0] ?? null);

        return $data;
    }

    private function getFirstAttribute(Entry $entity, string $attribute, $default = null)
    {
        return $entity->hasAttribute($attribute) ? $entity->getAttribute($attribute)[0] : $default;
    }

    private function parseMemberOf(array $rawGroups): array
    {
        $result = [];
        foreach ($rawGroups as $groupDn) {
            if (preg_match('/^CN=([^,]+)/i', $groupDn, $matches)) {
                $result[] = $matches[1];
            }
        }
        return $result;
    }

    private function convertAdTimestamp(?string $adTimestamp): ?string
    {
        if (empty($adTimestamp)) {
            return null;
        }

        $val = (float) $adTimestamp;
        $unixTimestamp = (int) ($val / 10000000) - 11644473600;
        return date('Y-m-d H:i:s', $unixTimestamp);
    }

    

}