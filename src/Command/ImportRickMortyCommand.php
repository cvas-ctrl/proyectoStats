<?php

namespace App\Command;

use App\Entity\Categoria;
use App\Entity\Elemento;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-rick-morty',
    description: 'Importa personajes y localizaciones evitando duplicados',
)]
class ImportRickMortyCommand extends Command
{
    private $httpClient;
    private $entityManager;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $entityManager)
    {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $responseChar = $this->httpClient->request('GET', 'https://rickandmortyapi.com/api/character', ['verify_peer' => false]);
        $dataChar = $responseChar->toArray();

        $catPersonajes = $this->entityManager->getRepository(Categoria::class)->findOneBy(['nombre' => 'Personajes']);
        if (!$catPersonajes) {
            $catPersonajes = new Categoria();
            $catPersonajes->setNombre('Personajes');
            $this->entityManager->persist($catPersonajes);
        }

        foreach ($dataChar['results'] as $charData) {
            $elemento = $this->entityManager->getRepository(Elemento::class)->findOneBy([
                'idApi' => $charData['id'],
                'categoria' => $catPersonajes
            ]);

            if (!$elemento) {
                $elemento = new Elemento();
                $elemento->setIdApi($charData['id']);
                $elemento->setCategoria($catPersonajes);
                $elemento->setPuntuacionPromedio(0);
                $elemento->setTotalValoraciones(0);
                $this->entityManager->persist($elemento);
            }

            $elemento->setNombre($charData['name']);
            $elemento->setImagenUrl($charData['image']);
            $elemento->setDatosExtra([
                'status' => $charData['status'],
                'species' => $charData['species']
            ]);
        }

        $responseLoc = $this->httpClient->request('GET', 'https://rickandmortyapi.com/api/location', ['verify_peer' => false]);
        $dataLoc = $responseLoc->toArray();

        $catLoc = $this->entityManager->getRepository(Categoria::class)->findOneBy(['nombre' => 'Localizaciones']);
        if (!$catLoc) {
            $catLoc = new Categoria();
            $catLoc->setNombre('Localizaciones');
            $this->entityManager->persist($catLoc);
        }

        foreach ($dataLoc['results'] as $locData) {
            $elemento = $this->entityManager->getRepository(Elemento::class)->findOneBy([
                'idApi' => $locData['id'],
                'categoria' => $catLoc
            ]);

            if (!$elemento) {
                $elemento = new Elemento();
                $elemento->setIdApi($locData['id']);
                $elemento->setCategoria($catLoc);
                $elemento->setPuntuacionPromedio(0);
                $elemento->setTotalValoraciones(0);
                $this->entityManager->persist($elemento);
            }

            $elemento->setNombre($locData['name']);
            $elemento->setImagenUrl('https://via.placeholder.com/300?text=Location');
            $elemento->setDatosExtra([
                'type' => $locData['type'],
                'dimension' => $locData['dimension']
            ]);
        }

        $this->entityManager->flush();

        $io->success('Importado');
        return Command::SUCCESS;
    }
}
