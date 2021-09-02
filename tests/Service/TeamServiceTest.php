<?php

namespace App\Tests\Service;

use App\Entity\Team;
use App\Service\TeamService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TeamServiceTest extends KernelTestCase
{

    public function testService(){

        self::bootKernel();
        $container = self::$container;

        $teamService = $container->get(TeamService::class);

        //ok
        $data = ['name' => 'liverpool', 'league' => 2];
        $team = $teamService->deserializeTeam($data, new Team());
        $this->assertEquals($data['name'], $team->getName());

        $team = $teamService->addLeague($data, $team);
        $this->assertEquals($data['league'], $team->getLeague()->getId());

        //bad league
        try{
            $teamService->addLeague(['league' => 1], $team);
        }catch (\Exception $e){
            $this->assertInstanceOf(\Exception::class, $e);
        }

        //remove league
        $team = $teamService->removeLeague($team);
        $this->assertEquals(null, $team->getLeague());

        //check duplicate -> OK
        $ret = $teamService->checkDuplicate($data['name']);
        $this->assertEquals(false, $ret);

        //check duplicate -> NOK
        try{
            $teamService->checkDuplicate('West Ham');
        }catch (\Exception $e){
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

}
