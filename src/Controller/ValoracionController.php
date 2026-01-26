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
        if (!$user) {
            $this->addFlash('danger', 'Debes estar logueado para votar.');
            return $this->redirectToRoute('app_login');
        }

        $puntuacion = $request->request->get('puntuacion');

        $valoracion = $em->getRepository(Valoracion::class)->findOneBy([
            'usuario' => $user,
            'elemento' => $elemento
        ]) ?? new Valoracion();

        $valoracion->setUsuario($user);
        $valoracion->setElemento($elemento);
        $valoracion->setPuntuacion((int)$puntuacion);
        $valoracion->setFechaCreacion(new \DateTimeImmutable());

        $em->persist($valoracion);
        $em->flush();

        $this->addFlash('success', 'Valoración guardada para ' . $elemento->getNombre() );

        return $this->redirect($request->headers->get('referer'));
    }
}
