<?php

namespace App\Controller;

use App\Entity\Team;
use App\Service\TeamService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamsController extends
    AbstractController
{

    /**
     * @Route("/teams", name="team_create", methods={"POST"})
     *
     * @param Request $request
     * @param TeamService $teamService
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     */
    public function createTeam(
        Request $request,
        TeamService $teamService
    ) {
        $data = \json_decode(
            $request->getContent(),
            true
        );

        try {
            $team = $teamService->deserializeTeam(
                $data,
                new Team()
            );
            $teamService->checkDuplicate(
                $team->getName()
            );

            //todo: league required or can be null ?

            //handle league
            if (empty($data['league'])) {
                return $this->json(
                    [
                        'success' => false,
                        'message' => 'missing league for team'
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $team = $teamService->addLeague(
                $data,
                $team
            );
        } catch (\Exception $e) {
            return $this->json(
                [
                    'success' => false,
                    'message' => $e->getMessage()
                ],
                $e->getCode()
            );
        }

        //save
        $teamService->persist(
            $team
        );

        //return response
        $serializedEntity = $teamService->serializeTeam(
            $team,
            ['read_team']
        );
        return new Response(
            $serializedEntity,
            Response::HTTP_CREATED,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @Route("/teams/{id}", name="team_update", methods={"PUT"})
     *
     * @param Request $request
     * @param TeamService $teamService
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     */
    public function updateTeam(
        Request $request,
        TeamService $teamService,
        int $id
    ) {
        //get team
        $team = $this->getDoctrine()->getManager()->getRepository(
            Team::class
        )->find(
            $id
        );
        if (!$team) {
            return $this->json(
                [
                    'success' => false,
                    'message' => 'No $team with this id !'
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        $data = \json_decode(
            $request->getContent(),
            true
        );

        try {
            if (!empty($data['name']) && $data['name'] != $team->getName()) {
                $teamService->checkDuplicate(
                    $data['name']
                );
            }

            $team = $teamService->deserializeTeam(
                $data,
                $team
            );

            //handle league (switch //todo: be able to remove ?)
            if (!empty($data['league'])) {
                $team = $teamService->removeLeague($team);

                $team = $teamService->addLeague(
                    $data,
                    $team
                );
            }
        } catch (\Exception $e) {
            return $this->json(
                [
                    'success' => false,
                    'message' => $e->getMessage()
                ],
                $e->getCode()
            );
        }

        //save
        $teamService->persist(
            $team
        );

        //return response
        $serializedEntity = $teamService->serializeTeam(
            $team,
            ['read_team']
        );
        return new Response(
            $serializedEntity,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }
}
