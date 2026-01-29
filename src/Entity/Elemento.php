<?php

namespace App\Entity;

use App\Repository\ElementoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ElementoRepository::class)]
#[ORM\Table(name: 'elementos', schema: 'proyectostats')]
class Elemento
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $idApi = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $imagenUrl = null;

    #[ORM\Column(nullable: true)]
    private ?array $datosExtra = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2)]
    private ?string $puntuacionPromedio = null;

    #[ORM\Column]
    private ?int $totalValoraciones = null;

    #[ORM\ManyToOne(inversedBy: 'elementos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categoria $categoria = null;

    /**
     * @var Collection<int, Valoracion>
     */
    #[ORM\OneToMany(targetEntity: Valoracion::class, mappedBy: 'elemento')]
    private Collection $valoracions;

    /**
     * @var Collection<int, RankingElemento>
     */
    #[ORM\OneToMany(targetEntity: RankingElemento::class, mappedBy: 'elemento')]
    private Collection $rankingElementos;

    public function __construct()
    {
        $this->valoracions = new ArrayCollection();
        $this->rankingElementos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdApi(): ?int
    {
        return $this->idApi;
    }

    public function setIdApi(int $idApi): static
    {
        $this->idApi = $idApi;

        return $this;
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

    public function getImagenUrl(): ?string
    {
        return $this->imagenUrl;
    }

    public function setImagenUrl(?string $imagenUrl): static
    {
        $this->imagenUrl = $imagenUrl;

        return $this;
    }

    public function getDatosExtra(): ?array
    {
        return $this->datosExtra;
    }

    public function setDatosExtra(?array $datosExtra): static
    {
        $this->datosExtra = $datosExtra;

        return $this;
    }

    public function getPuntuacionPromedio(): ?string
    {
        return $this->puntuacionPromedio;
    }

    public function setPuntuacionPromedio(string $puntuacionPromedio): static
    {
        $this->puntuacionPromedio = $puntuacionPromedio;

        return $this;
    }

    public function getTotalValoraciones(): ?int
    {
        return $this->totalValoraciones;
    }

    public function setTotalValoraciones(int $totalValoraciones): static
    {
        $this->totalValoraciones = $totalValoraciones;

        return $this;
    }

    public function getCategoria(): ?Categoria
    {
        return $this->categoria;
    }

    public function setCategoria(?Categoria $categoria): static
    {
        $this->categoria = $categoria;

        return $this;
    }

    /**
     * @return Collection<int, Valoracion>
     */
    public function getValoracions(): Collection
    {
        return $this->valoracions;
    }

    public function addValoracion(Valoracion $valoracion): static
    {
        if (!$this->valoracions->contains($valoracion)) {
            $this->valoracions->add($valoracion);
            $valoracion->setElemento($this);
        }

        return $this;
    }

    public function removeValoracion(Valoracion $valoracion): static
    {
        if ($this->valoracions->removeElement($valoracion)) {
            // set the owning side to null (unless already changed)
            if ($valoracion->getElemento() === $this) {
                $valoracion->setElemento(null);
            }
        }

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
            $rankingElemento->setElemento($this);
        }

        return $this;
    }

    public function removeRankingElemento(RankingElemento $rankingElemento): static
    {
        if ($this->rankingElementos->removeElement($rankingElemento)) {
            // set the owning side to null (unless already changed)
            if ($rankingElemento->getElemento() === $this) {
                $rankingElemento->setElemento(null);
            }
        }

        return $this;
    }
}
