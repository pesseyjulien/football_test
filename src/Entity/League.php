<?php

namespace App\Entity;

use App\Repository\LeagueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=LeagueRepository::class)
 */
class League
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read_league", "read_team"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read_league", "write_league", "read_team"})
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=Team::class, mappedBy="league", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Groups({"read_league"})
     */
    private $teams;

    public function __construct(
    )
    {
        $this->teams = new ArrayCollection();
    }

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

    /**
     * @return Collection|Team[]
     */
    public function getTeams(
    ): Collection
    {
        return $this->teams;
    }

    public function addTeam(
        Team $team
    ): self {
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
            $team->setLeague($this);
        }

        return $this;
    }

    public function removeTeam(
        Team $team
    ): self {
        if ($this->teams->removeElement($team)) {
            // set the owning side to null (unless already changed)
            if ($team->getLeague() === $this) {
                $team->setLeague(null);
            }
        }

        return $this;
    }
}
