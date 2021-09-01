<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TeamRepository::class)
 */
class Team
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read_team"})
     */
    private $id;

    /**
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255, unique=true)
     * @Groups({"read_team", "write_team"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read_team", "write_team"})
     */
    private $strip;

    /**
     * @ORM\ManyToOne(targetEntity=League::class, inversedBy="teams", cascade={"persist"})
     * @ORM\JoinColumn(name="league_id", referencedColumnName="id", nullable=false)
     * @Groups({"read_team"})
     */
    private $league;

    public function getId(
    ): ?int
    {
        return $this->id;
    }

    public function getName(
    ): ?string
    {
        return $this->name;
    }

    public function setName(
        string $name
    ): self {
        $this->name = $name;

        return $this;
    }

    public function getStrip(
    ): ?string
    {
        return $this->strip;
    }

    public function setStrip(
        ?string $strip
    ): self {
        $this->strip = $strip;

        return $this;
    }

    public function getLeague(
    ): ?League
    {
        return $this->league;
    }

    public function setLeague(
        ?League $league
    ): self {
        $this->league = $league;

        return $this;
    }
}
