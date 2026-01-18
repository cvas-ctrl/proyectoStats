<?php

namespace App\Command;

use App\Entity\Categoria;
use App\Entity\Elemento;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-rick-morty',
    description: 'Importamos los personajes',
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

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $response = $this->httpClient->request('GET', 'https://rickandmortyapi.com/api/character', [
                'verify_peer' => false,
            ]);

            $data = $response->toArray();

            $categoria = $this->entityManager->getRepository(Categoria::class)->findOneBy(['nombre' => 'Personajes']);

            if (!$categoria) {
                $categoria = new Categoria();
                $categoria->setNombre('Personajes');
                $this->entityManager->persist($categoria);
            }

            foreach ($data['results'] as $charData) {
                $elemento = new Elemento();
                $elemento->setNombre($charData['name']);
                $elemento->setCategoria($categoria);
                $elemento->setIdApi($charData['id']);

                $elemento->setImagenUrl($charData['image']);

                $elemento->setDatosExtra([
                    'status' => $charData['status'],
                    'species' => $charData['species'],
                    'gender' => $charData['gender']
                ]);

                $elemento->setPuntuacionPromedio(0);
                $elemento->setTotalValoraciones(0);

                $this->entityManager->persist($elemento);
            }

            $this->entityManager->flush();
            $io->success('Importado');

        } catch (\Exception $e) {
            $io->error('Error de conexión: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
