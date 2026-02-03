<?php

namespace App\Controller;

use App\Entity\Elemento;
use App\Entity\Valoracion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
final class ValoracionController extends AbstractController
{
    #[Route('/valorar/{id}', name: 'app_valorar', methods: ['POST'])]
    public function valorar(Elemento $elemento, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');

        $puntuacion = $request->request->get('puntuacion');
        $textoComentario = $request->request->get('comentario');

        $valoracion = $em->getRepository(Valoracion::class)->findOneBy([
            'usuario' => $user,
            'elemento' => $elemento
        ]) ?? new Valoracion();

        $valoracion->setUsuario($user);
        $valoracion->setElemento($elemento);
        $valoracion->setPuntuacion((int)$puntuacion);
        $valoracion->setComentario($textoComentario);
        $valoracion->setFechaCreacion(new \DateTimeImmutable());

        $em->persist($valoracion);
        $em->flush();

        $todasLasValoraciones = $elemento->getValoraciones();
        $totalVotos = count($todasLasValoraciones);
        $sumaPuntos = 0;

        foreach ($todasLasValoraciones as $v) {
            $sumaPuntos += $v->getPuntuacion();
        }

        $promedio = $totalVotos > 0 ? $sumaPuntos / $totalVotos : 0;

        $elemento->setTotalValoraciones($totalVotos);
        $elemento->setPuntuacionPromedio($promedio);

        $em->persist($elemento);
        $em->flush();

        $this->addFlash('success', 'Valoración guardada para ' . $elemento->getNombre());

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/mis-comentarios', name: 'app_mis_comentarios')]
    public function misComentarios(): Response {
        return $this->render('user/mis_comentarios.html.twig', [
            'valoraciones' => $this->getUser()->getValoraciones()
        ]);
    }

    #[Route('/comentario/borrar/{id}', name: 'app_comentario_borrar', methods: ['POST'])]
    public function borrarComentario(Valoracion $valoracion, EntityManagerInterface $em): Response {
        if ($valoracion->getUsuario() === $this->getUser()) {
            $elemento = $valoracion->getElemento();
            $em->remove($valoracion);
            $em->flush();

            $this->actualizarMediaElemento($elemento, $em);

            $this->addFlash('success', 'Comentario eliminado');
        }
        return $this->redirectToRoute('app_mis_comentarios');
    }

    #[Route('/comentario/editar/{id}', name: 'app_comentario_editar', methods: ['POST'])]
    public function editarComentario(Valoracion $valoracion, Request $request, EntityManagerInterface $em): Response {
        if ($valoracion->getUsuario() === $this->getUser()) {
            $valoracion->setComentario($request->request->get('comentario'));
            $valoracion->setPuntuacion((int)$request->request->get('puntuacion'));
            $valoracion->setFechaCreacion(new \DateTimeImmutable());

            $em->flush();

            $this->actualizarMediaElemento($valoracion->getElemento(), $em);

            $this->addFlash('success', 'Comentario actualizado');
        }
        return $this->redirectToRoute('app_mis_comentarios');
    }

    private function actualizarMediaElemento(Elemento $elemento, EntityManagerInterface $em): void
    {
        $todasLasValoraciones = $elemento->getValoraciones();
        $totalVotos = count($todasLasValoraciones);
        $sumaPuntos = 0;

        foreach ($todasLasValoraciones as $v) {
            $sumaPuntos += $v->getPuntuacion();
        }

        $promedio = $totalVotos > 0 ? $sumaPuntos / $totalVotos : 0;

        $elemento->setTotalValoraciones($totalVotos);
        $elemento->setPuntuacionPromedio($promedio);

        $em->persist($elemento);
        $em->flush();
    }
}
