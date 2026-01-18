<?php

namespace App\Entity;

use App\Repository\CategoriaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoriaRepository::class)]
#[ORM\Table(name: 'categorias', schema: 'proyectostats')]
class Categoria
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descripcion = null;

    /**
     * @var Collection<int, Elemento>
     */
    #[ORM\OneToMany(targetEntity: Elemento::class, mappedBy: 'categoria')]
    private Collection $elementos;

    public function __construct()
    {
        $this->elementos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

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

    /**
     * @return Collection<int, Elemento>
     */
    public function getElementos(): Collection
    {
        return $this->elementos;
    }

    public function addElemento(Elemento $elemento): static
    {
        if (!$this->elementos->contains($elemento)) {
            $this->elementos->add($elemento);
            $elemento->setCategoria($this);
        }

        return $this;
    }

    public function removeElemento(Elemento $elemento): static
    {
        if ($this->elementos->removeElement($elemento)) {
            // set the owning side to null (unless already changed)
            if ($elemento->getCategoria() === $this) {
                $elemento->setCategoria(null);
            }
        }

        return $this;
    }
}
