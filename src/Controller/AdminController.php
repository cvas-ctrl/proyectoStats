<?php

namespace App\Controller;

use App\Entity\Elemento;
use App\Entity\Categoria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_site')]
    public function index(): Response
    {
        return $this->render('admin/admin.html.twig');
    }

    #[Route('/admin/load', name: 'data_load_api')]
    public function data_load(HttpClientInterface $httpClient, EntityManagerInterface $entityManager): Response
    {
        $endpoints = [
            'Personajes' => 'https://rickandmortyapi.com/api/character',
            'Localizaciones' => 'https://rickandmortyapi.com/api/location',
            'Episodios' => 'https://rickandmortyapi.com/api/episode'
        ];

        foreach ($endpoints as $nombreCat => $url) {
            $categoria = $entityManager->getRepository(Categoria::class)->findOneBy(['nombre' => $nombreCat]);
            if (!$categoria) {
                $categoria = new Categoria();
                $categoria->setNombre($nombreCat);
                $entityManager->persist($categoria);
            }

            $response = $httpClient->request('GET', $url, ['verify_peer' => false]);
            $data = $response->toArray();

            foreach ($data['results'] as $item) {
                $elemento = $entityManager->getRepository(Elemento::class)->findOneBy([
                    'idApi' => $item['id'],
                    'categoria' => $categoria
                ]);

                if (!$elemento) {
                    $elemento = new Elemento();
                    $elemento->setIdApi($item['id']);
                    $elemento->setCategoria($categoria);
                    $elemento->setPuntuacionPromedio(0);
                    $elemento->setTotalValoraciones(0);
                    $entityManager->persist($elemento);
                }

                $elemento->setNombre($item['name']);

                if (isset($item['image'])) {
                    $imageUrl = $item['image'];
                } else {
                    $imageUrl = match ($nombreCat) {
                        'Localizaciones' => 'https://images.unsplash.com/photo-1462331940025-496dfbfc7564?w=800&auto=format',
                        'Episodios'      => 'https://images.unsplash.com/photo-1614728263952-84ea256f9679?w=800&auto=format',
                    };
                }
                $elemento->setImagenUrl($imageUrl);

                $extra = [];
                if ($nombreCat === 'Personajes') {
                    $extra = ['status' => $item['status'], 'species' => $item['species']];
                } elseif ($nombreCat === 'Localizaciones') {
                    $extra = ['type' => $item['type'], 'dimension' => $item['dimension']];
                } else {
                    $extra = ['air_date' => $item['air_date'], 'episode' => $item['episode']];
                }
                $elemento->setDatosExtra($extra);
            }
        }

        $entityManager->flush();

        $this->addFlash('success', 'Sincronización interdimensional completada con éxito');

        return $this->redirectToRoute('admin_site');
    }
}
