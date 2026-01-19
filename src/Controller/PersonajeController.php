<?php

namespace App\Controller;

use App\Entity\Elemento;
use App\Entity\Categoria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PersonajeController extends AbstractController
{
    #[Route('/ver/{categoriaNombre}', name: 'app_ver_categoria')]
    public function index(string $categoriaNombre, EntityManagerInterface $entityManager): Response
    {
        $categoria = $entityManager->getRepository(Categoria::class)
            ->findOneBy(['nombre' => $categoriaNombre]);

        if (!$categoria) {
            throw $this->createNotFoundException('La categoría "' . $categoriaNombre . '" no existe.');
        }

        $elementos = $entityManager->getRepository(Elemento::class)
            ->findBy(['categoria' => $categoria]);

        return $this->render('personaje/index.html.twig', [
            'elementos' => $elementos,
            'nombreCategoria' => $categoriaNombre,
        ]);
    }
}
