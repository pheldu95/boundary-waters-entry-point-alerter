<?php

namespace App\Command;

use App\Entity\EntryPoint;
use App\Repository\EntryPointRepository;
use App\Repository\MonitoredDateRepository;
use App\Services\SendAlertEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-email',
    description: 'Add a short description for your command',
)]
class TestEmailCommand extends Command
{
    public function __construct(
        private MonitoredDateRepository $monitoredDateRepository,
        private EntryPointRepository $entryPointRepository,
        private SendAlertEmail $sendAlertEmail
    )
    {
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

        $monitoredDates = $this->monitoredDateRepository->findAll();
        $entryPoints = $this->entryPointRepository->findAll();

        $this->sendAlertEmail->sendMonitoredDateAlert($monitoredDates[0], $entryPoints[0]);

        $io->success('Finished.');

        return Command::SUCCESS;
    }
}
