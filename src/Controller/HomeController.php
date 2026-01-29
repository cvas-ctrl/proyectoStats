<?php

namespace App\Controller;

use App\Repository\CategoriaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(CategoriaRepository $repo): Response
    {
        return $this->render('home/home.html.twig', [
            'categorias' => $repo->findAll(),
        ]);
    }
}
