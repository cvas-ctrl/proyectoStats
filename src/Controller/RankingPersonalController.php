<?php

namespace App\Controller;

use App\Entity\Ranking;
use App\Entity\RankingElemento;
use App\Repository\CategoriaRepository;
use App\Repository\ElementoRepository;
use App\Repository\ValoracionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/crear-mi-tier', name: 'app_ranking_nuevo')]
    public function nuevo(ElementoRepository $elRepo): Response
    {
        return $this->render('ranking_personal/creador.html.twig', [
            'elementos' => $elRepo->findAll()
        ]);
    }

    #[Route('/ranking/guardar', name: 'app_ranking_guardar', methods: ['POST'])]
    public function guardar(Request $request, EntityManagerInterface $em, ElementoRepository $elRepo): Response
    {
        $nombre = $request->request->get('nombre_ranking');
        $idsOrdenados = $request->request->get('elementos_seleccionados');

        if (!$nombre || !$idsOrdenados) {
            return $this->redirectToRoute('app_ranking_nuevo');
        }

        $ranking = new Ranking();
        $ranking->setNombre($nombre);
        $ranking->setUsuario($this->getUser());
        $em->persist($ranking);

        foreach ($idsOrdenados as $indice => $id) {
            $elemento = $elRepo->find($id);
            if ($elemento) {
                $re = new RankingElemento();
                $re->setRanking($ranking);
                $re->setElemento($elemento);
                $re->setPosicion($indice + 1);
                $em->persist($re);
            }
        }

        $em->flush();
        return $this->redirectToRoute('app_ranking_ver_final', ['id' => $ranking->getId()]);
    }

    #[Route('/ranking/ver/{id}', name: 'app_ranking_ver_final')]
    public function verFinal(Ranking $ranking): Response
    {
        if ($ranking->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('ranking_personal/ver_final.html.twig', [
            'ranking' => $ranking
        ]);
    }
}
