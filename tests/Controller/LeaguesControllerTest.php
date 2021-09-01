<?php

namespace App\Tests\Controller;

use ApiTestCase\JsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class LeaguesControllerTest extends JsonApiTestCase
{
    /**
     * @var mixed
     */
    private $token;

    protected function setUp() :void
    {
        $data = ['email' => 'test@yahoo.fr', 'password' => 'pass_1234'];

        $this->client->request('POST', '/users/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $response = $this->client->getResponse();

        $data = json_decode($response->getContent(), true);
        $this->token = $data['token'];
    }

    public function testGetTeamsFromLeague()
    {
        $this->client->request('GET', '/leagues/1/teams', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$this->token]);
        $response = $this->client->getResponse();
        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);

        $this->client->request('GET', '/leagues/2/teams', [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$this->token]);
        $response = $this->client->getResponse();
        $this->assertResponseCode($response,Response::HTTP_OK);
    }

}
