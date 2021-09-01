<?php

namespace App\Controller;

use App\Entity\League;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class LeaguesController extends
    AbstractController
{

    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
    private $em;

    /**
     * @param ManagerRegistry $manager
     */
    public function __construct(
        ManagerRegistry $manager
    ) {
        $this->em = $manager->getManager();
    }

    /**
     * @param $id
     * @return League|object|JsonResponse|null
     */
    public function getLeague(
        $id
    ) {
        //get league
        $league = $this->em->getRepository(
            League::class
        )->find(
            $id
        );
        if (!$league) {
            return $this->json(
                [
                    'success' => false,
                    'message' => 'No league with this id !'
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        return $league;
    }

    /**
     * @Route("/leagues/{id}/teams", name="league_teams", methods={"GET"})
     *
     * @param SerializerInterface $serializer
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     */
    public function getTeamsFromLeague(
        SerializerInterface $serializer,
        int $id
    ) {
        //todo: be able to get teams using leagues' name ?

        $league = $this->getLeague(
            $id
        );
        if ($league instanceof JsonResponse) {
            return $league;
        }

        //return response
        $serializedEntity = $serializer->serialize(
            $league->getTeams(),
            'json',
            array('groups' => ['read_team'])
        );
        return new Response(
            $serializedEntity,
            200,
            ['Content-Type' => 'application/json']
        );
    }


    /**
     * @Route("/leagues/{id}", name="league_delete", methods={"DELETE"})
     *
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteLeague(
        int $id
    ) {
        $league = $this->getLeague(
            $id
        );
        if ($league instanceof JsonResponse) {
            return $league;
        }

        //todo: don't remove teams as well ?

        $this->em->remove(
            $league
        );
        $this->em->flush();

        return $this->json(
            [
                'success' => true,
                'message' => 'League deleted successfully'
            ]
        );
    }

}
