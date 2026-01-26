<?php

namespace App\Controller;

use App\Repository\ValoracionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RankingController extends AbstractController
{
    #[Route('/ranking', name: 'app_ranking')]
    public function index(ValoracionRepository $repo): Response
    {
        $topElementos = $repo->createQueryBuilder('v')
            ->select('e.id, e.nombre, e.imagenUrl, AVG(v.puntuacion) as media, COUNT(v.id) as totalVotos')
            ->join('v.elemento', 'e')
            ->groupBy('e.id')
            ->orderBy('media', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->render('ranking/ranking.html.twig', [
            'elementos' => $topElementos
        ]);
    }
}
