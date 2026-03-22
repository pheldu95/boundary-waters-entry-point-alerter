<?php

namespace App\Command;

use App\Repository\EntryPointRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:map-divisions-to-entry-points',
    description: '',
)]
class MapDivisionsToEntryPointsCommand extends Command
{
    private string $projectDir;

    public function __construct(
        string $projectDir,
        private EntryPointRepository $entryPointRepository,
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
        $this->projectDir = $projectDir;
    }

    protected function configure(): void {}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        //the recreation.gov backend api calls each entry point a "division"
        $path = $this->projectDir . '/src/Data/permit-divisions.json';

        $jsonString = file_get_contents($path);

        if ($jsonString === false) {
            die('Error reading permit-divisions.json');
        }

        $divisionData = json_decode($jsonString, true);

        foreach ($divisionData['payload'] as $division) {
            $entryPoint = $this->entryPointRepository->findOneBy(['number' => $division['code']]);
            if (null !== $entryPoint) {
                $entryPoint->setDivisionId($division['id']);
                $this->em->persist($entryPoint);
            }
        }

        $this->em->flush();

        $io->success('Finished adding division IDs to each EntryPoint');

        return Command::SUCCESS;
    }
}
