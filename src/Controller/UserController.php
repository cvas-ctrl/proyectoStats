<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CategoriaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request                     $request,
                             UserPasswordHasherInterface $passwordHasher,
                             EntityManagerInterface      $entityManager): Response
    {

        if ($request->isMethod('POST')) {

            $new_user = new User();
            $new_user->setUsername($request->request->get('username'));
            $new_user->setEmail($request->request->get('email'));
            $password_text = $request->request->get('password');

            $hashedPassword = $passwordHasher->hashPassword(
                $new_user,
                $password_text
            );

            $new_user->setPassword($hashedPassword);

            $entityManager->persist($new_user);
            $entityManager->flush();

            return $this->redirectToRoute('app_ver_categoria', [
                'categoriaNombre' => 'Personajes'
            ]);

        }


        return $this->render('user/register.html.twig', [
        ]);
    }

    #[Route('/perfil', name: 'app_perfil')]
    public function perfil(CategoriaRepository $catRepo): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/perfil.html.twig', [
            'user' => $user,
            'categorias' => $catRepo->findAll(),
        ]);
    }

    #[Route('/mis-estadisticas', name: 'app_mis_stats')]
    public function misEstadisticas(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');

        $misTopElementos = $em->createQuery('
        SELECT e.nombre, e.imagenUrl, v.puntuacion as nota, c.nombre as catNombre
        FROM App\Entity\Valoracion v
        JOIN v.elemento e
        JOIN e.categoria c
        WHERE v.usuario = :user
        ORDER BY v.puntuacion DESC
    ')
            ->setParameter('user', $user)
            ->setMaxResults(10)
            ->getResult();

        $misPromedios = $em->createQuery('
        SELECT c.nombre as nombre,
               AVG(v.puntuacion) as promedio,
               COUNT(v.id) as totalVotos
        FROM App\Entity\Valoracion v
        JOIN v.elemento e
        JOIN e.categoria c
        WHERE v.usuario = :user
        GROUP BY c.id
    ')
            ->setParameter('user', $user)
            ->getResult();

        $miTotalVotos = count($user->getValoraciones());

        return $this->render('user/mis_stats.html.twig', [
            'topElementos' => $misTopElementos,
            'promedios' => $misPromedios,
            'totalVotos' => $miTotalVotos
        ]);
    }

}
