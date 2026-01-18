<?php

namespace App\Entity;

use App\Repository\RankingElementoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RankingElementoRepository::class)]
#[ORM\Table(name: 'ranking_elementos', schema: 'proyectostats')]
class RankingElemento
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $posicion = null;

    #[ORM\ManyToOne(inversedBy: 'rankingElementos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ranking $ranking = null;

    #[ORM\ManyToOne(inversedBy: 'rankingElementos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Elemento $elemento = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosicion(): ?int
    {
        return $this->posicion;
    }

    public function setPosicion(int $posicion): static
    {
        $this->posicion = $posicion;

        return $this;
    }

    public function getRanking(): ?Ranking
    {
        return $this->ranking;
    }

    public function setRanking(?Ranking $ranking): static
    {
        $this->ranking = $ranking;

        return $this;
    }

    public function getElemento(): ?Elemento
    {
        return $this->elemento;
    }

    public function setElemento(?Elemento $elemento): static
    {
        $this->elemento = $elemento;

        return $this;
    }
}
