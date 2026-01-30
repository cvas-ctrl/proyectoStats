<?php

namespace App\Controller;

use App\Repository\CategoriaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function home(CategoriaRepository $categoriaRepository): Response
    {
        $categorias = $categoriaRepository->findBy([], ['nombre' => 'ASC']);

        return $this->render('home/home.html.twig', [
            'categorias' => $categorias
        ]);
    }
}
