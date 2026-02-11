<?php

namespace App\Controller;

use App\Entity\Ranking;
use App\Entity\RankingElemento;
use App\Repository\CategoriaRepository;
use App\Repository\ElementoRepository;
use App\Repository\RankingRepository;
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
    public function nuevo(ElementoRepository $elRepo, CategoriaRepository $catRepo): Response
    {
        return $this->render('ranking_personal/creador.html.twig', [
            'elementos' => $elRepo->findAll(),
            'categorias' => $catRepo->findAll()
        ]);
    }

    #[Route('/procesar-mi-ranking', name: 'app_ranking_guardar', methods: ['POST'])]
    public function guardar(Request $request, EntityManagerInterface $em, ElementoRepository $elRepo, RankingRepository $rankingRepo): Response
    {
        $nombre = $request->request->get('nombre_ranking');
        $usuario = $this->getUser();
        $params = $request->request->all();
        $idsOrdenados = $params['elementos_seleccionados'] ?? [];

        if (!$nombre || empty($idsOrdenados)) {
            $this->addFlash('warning', 'El ranking debe tener un nombre y al menos un elemento');
            return $this->redirectToRoute('app_ranking_nuevo');
        }

        $existe = $rankingRepo->findOneBy([
            'usuario' => $usuario,
            'nombreRanking' => $nombre
        ]);

        if ($existe) {
            $this->addFlash('danger', 'Ya has creado un ranking con el nombre "' . $nombre . '". Prueba con otro o edita el anterior');
            return $this->redirectToRoute('app_ranking_nuevo');
        }

        $ranking = new Ranking();
        $ranking->setNombreRanking($nombre);
        $ranking->setUsuario($usuario);

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

        $this->addFlash('success', 'Ranking personalizado');
        return $this->redirectToRoute('app_ranking_ver_final', ['id' => $ranking->getId()]);
    }

    #[Route('/ranking/editar/{id}', name: 'app_ranking_editar')]
    public function editar(Ranking $ranking, Request $request, EntityManagerInterface $em, ElementoRepository $elRepo, CategoriaRepository $catRepo): Response
    {
        if ($ranking->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No puedes editar este ranking');
        }

        if ($request->isMethod('POST')) {
            $nuevoNombre = $request->request->get('nombre_ranking');
            $idsOrdenados = $request->request->all()['elementos_seleccionados'] ?? [];

            if ($nuevoNombre && !empty($idsOrdenados)) {
                $ranking->setNombreRanking($nuevoNombre);

                foreach ($ranking->getRankingElementos() as $antiguo) {
                    $em->remove($antiguo);
                }
                $em->flush();

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
                $this->addFlash('success', 'Ranking actualizado');
                return $this->redirectToRoute('app_mis_rankings_lista');
            }
        }

        return $this->render('ranking_personal/editar.html.twig', [
            'ranking' => $ranking,
            'elementos' => $elRepo->findAll(),
            'categorias' => $catRepo->findAll()
        ]);
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

    #[Route('/mis-rankings-lista', name: 'app_mis_rankings_lista')]
    public function listaRankings(RankingRepository $rankingRepo): Response
    {
        $user = $this->getUser();

        $misRankings = $rankingRepo->findBy(
            ['usuario' => $user],
            ['fechaCreacion' => 'DESC']
        );

        return $this->render('ranking_personal/mis_listas.html.twig', [
            'rankings' => $misRankings
        ]);
    }
}
