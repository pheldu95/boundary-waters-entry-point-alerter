<?php
namespace App\MessageHandler;

use App\Message\ScrapeEntryPointForPermitMessage;
use App\Services\SendPermitAlertEmail;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Services\WebScrapingClient;
use DateTimeImmutable;

#[AsMessageHandler]
class ScrapeEntryPointForPermitMessageHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private SendPermitAlertEmail $sendPermitAlertEmail
    ) {}

    public function __invoke(ScrapeEntryPointForPermitMessage $message)
    {
        $this->logger->info('ScrapeEntryPointForPermitMessage received!');
        $this->logger->info('Message received!', [
            'entryPoint' => $message->getPermitWatch()->getEntryPoint(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $permitWatch = $message->getPermitWatch();
        $targetDate = $permitWatch->getTargetDate();
        $formattedTargetDate = $targetDate->format('Y-m-d\TH:i:s\Z');

        //Just to see that it's working
        // TODO: use the actual logger
        dump('Handler processing: ' . $permitWatch->getEntryPoint()->getName());
        dump('Permit target date: ' . $permitWatch->getTargetDate()->format('Y-m-d'));

        $webScrapingClient = new WebScrapingClient();

        $firstOfMonth = $targetDate->modify('first day of this month midnight')->format('Y-m-d\TH:i:s.v\Z');

        $recreationDotGovUrl = "https://www.recreation.gov/api/permits/233396/availability/month?start_date=" . $firstOfMonth ."&commercial_acct=false";
        
        $result = $webScrapingClient->scrapeJson($recreationDotGovUrl);

        $divisionId = $permitWatch->getEntryPoint()->getDivisionId();
        $dateAvailability = $result['payload']['availability'][$divisionId]['date_availability'][$formattedTargetDate];

        if($dateAvailability['remaining'] > 0)
        {
            dump('Permit available. Sending alert email to: ' . $permitWatch->getUser()->getEmail());
            $this->sendPermitAlertEmail->sendPermitAlert($message->getPermitWatch());
        }
        else
        {
            dump('No permits available. End.');
        }
    }
}