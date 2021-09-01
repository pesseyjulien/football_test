<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthentificationControllerTest extends WebTestCase
{

    public function testAuthentification(): void
    {
        $client = static::createClient();
        $client->request('GET', '/leagues/1/teams');
        $this->assertResponseStatusCodeSame(401);
    }
}
