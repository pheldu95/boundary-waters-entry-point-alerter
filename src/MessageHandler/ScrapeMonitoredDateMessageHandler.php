<?php

namespace App\MessageHandler;

use App\Entity\EntryPointAvailability;
use App\Message\ScrapeEntryPointForPermitMessage;
use App\Message\ScrapeMonitoredDateMessage;
use App\Repository\EntryPointAvailabilityRepository;
use App\Repository\EntryPointRepository;
use App\Services\SendPermitAlertEmail;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Services\WebScrapingClient;
use DateTimeImmutable;

#[AsMessageHandler]
class ScrapeMonitoredDateMessageHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private SendPermitAlertEmail $sendPermitAlertEmail,
        private EntryPointRepository $entryPointRepository,
        private EntryPointAvailabilityRepository $entryPointAvailabilityRepository,
    ) {}

    public function __invoke(ScrapeMonitoredDateMessage $message)
    {
        $this->logger->info('ScrapeMonitoredDateMessage received!', [
            'monitoredDate id' => $message->getMonitoredDate()->getId(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $monitoredDate = $message->getmonitoredDate();
        $targetDate = $monitoredDate->getDate();
        $formattedTargetDate = $targetDate->format('Y-m-d\TH:i:s\Z');

        $this->logger->info('Target date: ' . $monitoredDate->getDate()->format('Y-m-d'), [
            'monitoredDate id' => $message->getMonitoredDate()->getId(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $webScrapingClient = new WebScrapingClient();

        $firstOfMonth = $targetDate->modify('first day of this month midnight')->format('Y-m-d\TH:i:s.v\Z');

        $recreationDotGovUrl = "https://www.recreation.gov/api/permits/233396/availability/month?start_date=" . $firstOfMonth . "&commercial_acct=false";

        $scrapedData = $webScrapingClient->scrapeJson($recreationDotGovUrl);

        //get all entry points
        $entryPoints = $this->entryPointRepository->findAll();
        foreach($entryPoints as $entryPoint) {
            $divisionId = $entryPoint->getDivisionId();
            $availablePermitCount = $this->getAvailablePermitCount($divisionId, $scrapedData, $formattedTargetDate);
            
            //see if EntryPointAvailibility exists yet for this date and entry point:
            $entryPointAvailability = $this->entryPointAvailabilityRepository->findOneBy([
                'entryPoint' => $entryPoint,
                'monitoredDate' => $monitoredDate
            ]);
            
            if($entryPointAvailability === null) {
                $entryPointAvailability = new EntryPointAvailability();
                
                $entryPointAvailability->setEntryPoint($entryPoint);
                $entryPointAvailability->setMonitoredDate($monitoredDate);
                $entryPointAvailability->setAvailableCount($availablePermitCount);
            }

            if(
                $entryPointAvailability->getAvailableCount() === 0
                && $availablePermitCount > 0
            ) {
                $entryPointAvailability->setAvailableCount($availablePermitCount);

                //send alert
                
            }
        }
        // $divisionId = $permitWatch->getEntryPoint()->getDivisionId();
        // $dateAvailability = $result['payload']['availability'][$divisionId]['date_availability'][$formattedTargetDate];

        // if ($dateAvailability['remaining'] > 0) {
        //     $this->logger->notice('Permit available.' . $permitWatch->getUser()->getEmail(), [
        //         'permitWatchId' => $message->getPermitWatch()->getId(),
        //         'timestamp' => date('Y-m-d H:i:s')
        //     ]);
            
        //     $this->sendPermitAlertEmail->sendPermitAlert($message->getPermitWatch());
        // } else {
        //     $this->logger->alert('No permits available. End.', [
        //         'permitWatchId' => $message->getPermitWatch()->getId(),
        //         'timestamp' => date('Y-m-d H:i:s')
        //     ]);
        // }
    }

    private function getAvailablePermitCount(
        int $divisionId, 
        array $scrapedData,
        string $formattedTargetDate
    ): int
    {
        $dateAvailability = $scrapedData['payload']['availability'][$divisionId]['date_availability'][$formattedTargetDate];
        
        return $dateAvailability['remaining'];
    }
}
