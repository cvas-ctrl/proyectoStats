<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ElementoController extends AbstractController
{
    #[Route('/ver/{categoriaNombre}', name: 'app_ver_categoria')]
    public function index(string $categoriaNombre, HttpClientInterface $httpClient): Response
    {
        $apiUrl = match (strtolower($categoriaNombre)) {
            'personajes' => 'https://rickandmortyapi.com/api/character',
            'localizaciones' => 'https://rickandmortyapi.com/api/location',
            'episodios' => 'https://rickandmortyapi.com/api/episode',
            default => null
        };

        if (!$apiUrl) {
            throw $this->createNotFoundException('La categoría "' . $categoriaNombre . '" no es válida.');
        }

        $response = $httpClient->request('GET', $apiUrl, [
            'verify_peer' => false
        ]);

        $data = $response->toArray();
        $elementos = $data['results'];

        return $this->render('elemento/index.html.twig', [
            'elementos' => $elementos,
            'nombreCategoria' => ucfirst($categoriaNombre),
        ]);
    }
}
