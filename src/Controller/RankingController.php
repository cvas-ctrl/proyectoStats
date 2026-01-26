<?php

namespace App\Controller;

use App\Repository\CategoriaRepository;
use App\Repository\ValoracionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RankingController extends AbstractController
{
    #[Route('/ranking/{categoria}', name: 'app_ranking', defaults: ['categoria' => 'Personajes'])]
    public function index(string $categoria, ValoracionRepository $repo, CategoriaRepository $catRepo): Response
    {
        $todasCategorias = $catRepo->findAll();

        $topElementos = $repo->createQueryBuilder('v')
            ->select('e.id, e.nombre, e.imagenUrl, AVG(v.puntuacion) as media, COUNT(v.id) as totalVotos')
            ->join('v.elemento', 'e')
            ->join('e.categoria', 'c')
            ->where('c.nombre = :catNombre')
            ->setParameter('catNombre', $categoria)
            ->groupBy('e.id, e.nombre, e.imagenUrl')
            ->orderBy('media', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->render('ranking/ranking.html.twig', [
            'elementos' => $topElementos,
            'categoriaActual' => $categoria,
            'categorias' => $todasCategorias
        ]);
    }
}
