<?php

namespace App\Tests\Controller;

use ApiTestCase\JsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class TeamsControllerTest extends JsonApiTestCase
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

    public function testCreateTeam(){

        $headers = ['HTTP_AUTHORIZATION' => 'Bearer '.$this->token ,'CONTENT_TYPE' => 'application/json'];

        //unauthorized
        $this->client->request('POST', '/teams', [], [], []);
        $response = $this->client->getResponse();
        $this->assertResponseCode($response, Response::HTTP_UNAUTHORIZED);

        //empty data
        $this->client->request('POST', '/teams', [], [], $headers, '');
        $response = $this->client->getResponse();
        $this->assertResponseCode($response, Response::HTTP_BAD_REQUEST);

        //bad league
        $data = ['name' => 'liverpool', 'league' => 1];
        $this->client->request('POST', '/teams', [], [], $headers, json_encode($data));
        $response = $this->client->getResponse();
        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);

        //ok
        $data = ['name' => 'liverpool', 'league' => 2];
        $this->client->request('POST', '/teams', [], [], $headers, json_encode($data));
        $response = $this->client->getResponse();
        $this->assertResponseCode($response, Response::HTTP_CREATED);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('liverpool', $data['name']);
        $this->assertEquals(2, $data['league']['id']);

        //duplicate
        $data = ['name' => 'liverpool', 'league' => 2];
        $this->client->request('POST', '/teams', [], [], $headers, json_encode($data));
        $response = $this->client->getResponse();
        $this->assertResponseCode($response, Response::HTTP_CONFLICT);
    }

    public function testUpdateTeam(){

        $headers = ['HTTP_AUTHORIZATION' => 'Bearer '.$this->token ,'CONTENT_TYPE' => 'application/json'];

        //unauthorized
        $this->client->request('PUT', '/teams/7', [], [], []);
        $response = $this->client->getResponse();
        $this->assertResponseCode($response, Response::HTTP_UNAUTHORIZED);

        //empty data
        $this->client->request('PUT', '/teams/7', [], [], $headers, '');
        $response = $this->client->getResponse();
        $this->assertResponseCode($response, Response::HTTP_BAD_REQUEST);

        //bad team
        $data = ['name' => 'liverpool', 'league' => 1];
        $this->client->request('PUT', '/teams/4', [], [], $headers, json_encode($data));
        $response = $this->client->getResponse();
        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);

        //change name -> ok
        $data = ['name' => 'liverpool2'];
        $this->client->request('PUT', '/teams/7', [], [], $headers, json_encode($data));
        $response = $this->client->getResponse();
        $this->assertResponseCode($response, Response::HTTP_OK);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('liverpool2', $data['name']);

        //change league -> nok
        $data = ['league' => 1];
        $this->client->request('PUT', '/teams/7', [], [], $headers, json_encode($data));
        $response = $this->client->getResponse();
        $this->assertResponseCode($response, Response::HTTP_NOT_FOUND);

        //todo: change league -> ok
    }
}
