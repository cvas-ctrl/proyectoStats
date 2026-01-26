<?php

namespace App\Controller;

use App\Repository\CategoriaRepository;
use App\Repository\ValoracionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RankingPersonalController extends AbstractController
{
    #[Route('/mi_ranking/{categoria}', name: 'app_ranking_personal', defaults: ['categoria' => 'Personajes'])]
    public function miRanking(string $categoria, ValoracionRepository $repo, CategoriaRepository $catRepo): Response
    {
        $user = $this->getUser();
        $todasCategorias = $catRepo->findAll();

        $misVotos = $repo->createQueryBuilder('v')
            ->select('e.id, e.nombre, e.imagenUrl, v.puntuacion as nota, v.fechaCreacion')
            ->join('v.elemento', 'e')
            ->join('e.categoria', 'c')
            ->where('v.usuario = :usuario')
            ->andWhere('c.nombre = :catNombre')
            ->setParameter('usuario', $user)
            ->setParameter('catNombre', $categoria)
            ->orderBy('v.puntuacion', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('ranking_personal/ranking_personal.html.twig', [
            'votos' => $misVotos,
            'categoriaActual' => $categoria,
            'categorias' => $todasCategorias
        ]);
    }
}
