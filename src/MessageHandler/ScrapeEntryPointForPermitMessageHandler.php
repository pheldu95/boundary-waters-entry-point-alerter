<?php

namespace App\MessageHandler;

use App\Message\ScrapeEntryPointForPermitMessage;
use App\Services\SendAlertEmail;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Services\WebScrapingClient;
use DateTimeImmutable;

#[AsMessageHandler]
class ScrapeEntryPointForPermitMessageHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private SendAlertEmail $sendAlertEmail,
    ) {}

    public function __invoke(ScrapeEntryPointForPermitMessage $message)
    {
        $this->logger->info('ScrapeEntryPointForPermitMessage received!', [
            'permitWatchId' => $message->getPermitWatch()->getId(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $permitWatch = $message->getPermitWatch();
        $targetDate = $permitWatch->getTargetDate();
        $formattedTargetDate = $targetDate->format('Y-m-d\TH:i:s\Z');

        //Just to see that it's working
        // TODO: use the actual logger
        $this->logger->info('Handler processing: ' . $permitWatch->getEntryPoint()->getName(), [
            'permitWatchId' => $message->getPermitWatch()->getId(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        $this->logger->info('Permit target date: ' . $permitWatch->getTargetDate()->format('Y-m-d'), [
            'permitWatchId' => $message->getPermitWatch()->getId(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $webScrapingClient = new WebScrapingClient();

        $firstOfMonth = $targetDate->modify('first day of this month midnight')->format('Y-m-d\TH:i:s.v\Z');

        $recreationDotGovUrl = "https://www.recreation.gov/api/permits/233396/availability/month?start_date=" . $firstOfMonth . "&commercial_acct=false";

        $result = $webScrapingClient->scrapeJson($recreationDotGovUrl);

        $divisionId = $permitWatch->getEntryPoint()->getDivisionId();
        $dateAvailability = $result['payload']['availability'][$divisionId]['date_availability'][$formattedTargetDate];

        if ($dateAvailability['remaining'] > 0) {
            $this->logger->notice('Permit available.' . $permitWatch->getUser()->getEmail(), [
                'permitWatchId' => $message->getPermitWatch()->getId(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
            $this->sendAlertEmail->sendPermitAlert($message->getPermitWatch());
        } else {
            $this->logger->alert('No permits available. End.', [
                'permitWatchId' => $message->getPermitWatch()->getId(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }
}
