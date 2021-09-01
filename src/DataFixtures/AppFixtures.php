<?php

namespace App\DataFixtures;

use App\Entity\League;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(
        ObjectManager $manager)
    {
        //user
        $user = new User();
        $user->setEmail('test@yahoo.fr');
        $password = $this->encoder->encodePassword($user, 'pass_1234');
        $user->setPassword($password);
        $manager->persist($user);

        //league
        $league = new League();
        $league->setName('Premier League');
        $manager->persist($league);

        //teams
        $clubs = ['Arsenal', 'Newcastle', 'Fulham', 'Manchester United', 'West Ham', 'AFC Richmond'];
        foreach ($clubs as $club){

            $team = new Team();
            $team->setName($club);
            $team->setLeague($league);
            $league->addTeam($team);
            $manager->persist($team);
        }

        $manager->flush();
    }
}
