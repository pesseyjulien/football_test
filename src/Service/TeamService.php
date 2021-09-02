<?php

namespace App\Service;

use App\Entity\League;
use App\Entity\Team;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TeamService
{

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ManagerRegistry
     */
    private $manager;

    /**
     * @param ValidatorInterface $validator
     * @param ManagerRegistry $manager
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ValidatorInterface $validator,
        ManagerRegistry $manager,
        SerializerInterface $serializer
    ) {
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->manager = $manager->getManager();
    }

    /**
     * @param Team $team
     */
    public function persist(
        Team $team
    ) {
        $this->manager->persist($team);
        $this->manager->flush();
    }

    /**
     * @param Team $team
     * @param $groups
     * @return string
     */
    public function serializeTeam(
        Team $team,
        $groups
    ) {
        return $this->serializer->serialize($team,
            'json',
            array('groups' => $groups));
    }

    /**
     * @param $data
     * @param Team $team
     * @return Team
     * @throws \Exception
     */
    public function deserializeTeam(
        $data,
        Team $team
    ) {
        if (empty($data)) {
            throw new \Exception('empty params',
                Response::HTTP_BAD_REQUEST);
        }

        /** @var Team $team */
        $team = $this->serializer->deserialize(\json_encode(\array_filter($data)),
            Team::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $team]);

        //validate team
        $errors = $this->validator->validate($team);
        if (count($errors) > 0) {
            throw new \Exception($errors,
                Response::HTTP_BAD_REQUEST);
        }

        return $team;
    }

    /**
     * @param $name
     * @throws \Exception
     */
    public function checkDuplicate(
        $name
    ) {
        $check = $this->manager->getRepository(Team::class)->findOneBy(['name' => $name]);
        if ($check) {
            throw new \Exception('Team with same name already exists',
                Response::HTTP_CONFLICT);
        }
    }

    /**
     * @param $data
     * @param Team $team
     * @return Team
     * @throws \Exception
     */
    public function addLeague(
        $data,
        Team $team
    ) {
        //get league
        $league = $this->manager->getRepository(League::class)->find($data['league']);
        if (!$league) {
            throw new \Exception('League does not exist',
                Response::HTTP_NOT_FOUND);
        }

        $team->setLeague($league);
        $league->addTeam($team);

        return $team;
    }

    /**
     * @param Team $team
     * @return Team
     */
    public function removeLeague(Team $team){

        $league = $team->getLeague();
        if ($league) {
            $league->removeTeam(
                $team
            );
        }

        return $team;
    }
}
