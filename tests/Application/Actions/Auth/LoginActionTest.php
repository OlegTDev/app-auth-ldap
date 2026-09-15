<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Auth;

use App\Application\Services\LdapService;
use DI\Container;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Tests\TestCase;

class LoginActionTest extends TestCase
{
    public function testAction()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $fakeLdapData = [
            'cn' => 'Bill Gates',
            'userPrincipalName' => 'bill.gates@company.local',
            'sn' => 'Gates',
            'displayName' => 'Bill Gates',
            'mail' => 'bill@company.local',
            'memberOf' => ['Domain Admins'],
            'company' => 'Microsoft',
            'department' => 'IT',
            'title' => 'CEO',
            'physicalDeliveryOfficeName' => 'Redmond',
            'telephoneNumber' => '12345',
            'streetAddress' => 'One Microsoft Way',
            'postalCode' => '98052',
            'st' => 'WA',
            'ipPhone' => '54321',
            'countryCode' => 'US',
            'lastLogon' => '2026-09-15 12:00:00'
        ];

        $ldapServiceMock = $this->createMock(LdapService::class);
        $ldapServiceMock->expects($this->once())
            ->method('query')
            ->with('admin')
            ->willReturn($fakeLdapData);

        $container->set(LdapService::class, $ldapServiceMock);       
        
        $request = $this->createRequest(method: 'GET', path: '/', serverParams: ['REMOTE_USER' => 'COMPANY\admin'])
            ->withQueryParams(['return_url' => 'http://example.com']);
        
        $response = $app->handle($request);
        
        $redirectUrl = $response->getHeader('Location')[0];
        $this->assertNotEmpty($redirectUrl);
        $this->assertEquals(302, $response->getStatusCode());
        
        $tokenString = parse_url($redirectUrl, PHP_URL_QUERY);
        parse_str($tokenString, $result);

        $tokenPayload = $this->decodeJwt($result['token']);
        $this->assertEquals('admin', $tokenPayload->sAMAccountName);
        $this->assertEquals('COMPANY', $tokenPayload->domain);
        $this->assertEquals($fakeLdapData['cn'], $tokenPayload->cn);
        $this->assertEquals($fakeLdapData['memberOf'], $tokenPayload->memberOf);

    }

    private function decodeJwt(string $data): \stdClass
    {
        $secretKey = env('JWT_SECRET', str_repeat('1', 32));
        return JWT::decode($data, new Key($secretKey, 'HS256'));
    }
}
