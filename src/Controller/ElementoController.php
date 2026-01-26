<?php

namespace App\Controller;
use App\Entity\Categoria;
use App\Entity\Elemento;
use App\Repository\ElementoRepository;
use App\Repository\CategoriaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ElementoController extends AbstractController
{
    #[Route('/ver/{categoriaNombre}', name: 'app_ver_categoria')]
    public function index(
        string $categoriaNombre,
        ElementoRepository $elementoRepository,
        CategoriaRepository $categoriaRepository
    ): Response {
        $categoria = $categoriaRepository->findOneBy(['nombre' => $categoriaNombre]);

        if (!$categoria) {
            throw $this->createNotFoundException('La categoría no existe');
        }

        $elementos = $elementoRepository->findBy(['categoria' => $categoria]);

        return $this->render('elemento/index.html.twig', [
            'elementos' => $elementos,
            'nombreCategoria' => $categoriaNombre
        ]);
    }

    #[Route('/elemento/{id}', name: 'app_elemento_detalle')]
    public function detalle(Elemento $elemento): Response
    {
        return $this->render('elemento/detalle.html.twig', [
            'el' => $elemento
        ]);
    }
}
