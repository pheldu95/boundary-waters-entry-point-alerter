<?php

namespace App\Command;

use App\Message\ScrapeMonitoredDateMessage;
use App\Repository\MonitoredDateRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:check-monitored-dates',
    description: 'Add a short description for your command',
)]
class CheckMonitoredDatesCommand extends Command
{
    public function __construct(
        private MonitoredDateRepository $monitoredDateRepository,
        private MessageBusInterface $bus
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        // $this
        //     ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
        //     ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        // ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $monitoredDates = $this->monitoredDateRepository->findAll();

        foreach ($monitoredDates as $monitoredDate) {
            $this->bus->dispatch(new ScrapeMonitoredDateMessage($monitoredDate->getId()));
        
            $io->info('Dispatching message for MonitoredDate ID: ' . $monitoredDate->getId());
        }
        $io->success('Finished queueing monitored dates.');

        return Command::SUCCESS;
    }
}
