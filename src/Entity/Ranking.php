<?php

namespace App\Entity;

use App\Repository\RankingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RankingRepository::class)]
#[ORM\Table(name: 'rankings_personales', schema: 'proyectostats')]
class Ranking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nombreRanking = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fechaCreacion = null;

    #[ORM\ManyToOne(inversedBy: 'rankings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $usuario = null;

    /**
     * @var Collection<int, RankingElemento>
     */
    #[ORM\OneToMany(targetEntity: RankingElemento::class, mappedBy: 'ranking', orphanRemoval: true)]
    private Collection $rankingElementos;

    public function __construct()
    {
        $this->rankingElementos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreRanking(): ?string
    {
        return $this->nombreRanking;
    }

    public function setNombreRanking(string $nombreRanking): static
    {
        $this->nombreRanking = $nombreRanking;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getFechaCreacion(): ?\DateTimeImmutable
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTimeImmutable $fechaCreacion): static
    {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    public function getUsuario(): ?User
    {
        return $this->usuario;
    }

    public function setUsuario(?User $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * @return Collection<int, RankingElemento>
     */
    public function getRankingElementos(): Collection
    {
        return $this->rankingElementos;
    }

    public function addRankingElemento(RankingElemento $rankingElemento): static
    {
        if (!$this->rankingElementos->contains($rankingElemento)) {
            $this->rankingElementos->add($rankingElemento);
            $rankingElemento->setRanking($this);
        }

        return $this;
    }

    public function removeRankingElemento(RankingElemento $rankingElemento): static
    {
        if ($this->rankingElementos->removeElement($rankingElemento)) {
            // set the owning side to null (unless already changed)
            if ($rankingElemento->getRanking() === $this) {
                $rankingElemento->setRanking(null);
            }
        }

        return $this;
    }
}
