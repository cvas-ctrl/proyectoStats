<?php

namespace App\Controller;

use App\Entity\User;
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
}
