<?php

namespace App\MessageHandler;

use App\Entity\EntryPointAvailability;
use App\Message\ScrapeEntryPointForPermitMessage;
use App\Message\ScrapeMonitoredDateMessage;
use App\Repository\EntryPointAvailabilityRepository;
use App\Repository\EntryPointRepository;
use App\Repository\MonitoredDateRepository;
use App\Services\SendAlertEmail;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Services\WebScrapingClient;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

#[AsMessageHandler]
class ScrapeMonitoredDateMessageHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private SendAlertEmail $sendAlertEmail,
        private EntryPointRepository $entryPointRepository,
        private EntryPointAvailabilityRepository $entryPointAvailabilityRepository,
        private EntityManagerInterface $entityManager,
        private MonitoredDateRepository $monitoredDateRepository,
    ) {}

    public function __invoke(ScrapeMonitoredDateMessage $message)
    {
        $this->logger->info('ScrapeMonitoredDateMessage received!', [
            'monitoredDate id' => $message->monitoredDateId,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $monitoredDate = $this->monitoredDateRepository->find($message->monitoredDateId);
        $targetDate = $monitoredDate->getDate();
        $formattedTargetDate = $targetDate->format('Y-m-d\TH:i:s\Z');

        $this->logger->info('Target date: ' . $monitoredDate->getDate()->format('Y-m-d'), [
            'monitoredDate id' => $message->monitoredDateId,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $webScrapingClient = new WebScrapingClient();

        $firstOfMonth = $targetDate->modify('first day of this month midnight')->format('Y-m-d\TH:i:s.v\Z');

        $recreationDotGovUrl = "https://www.recreation.gov/api/permits/233396/availability/month?start_date=" . $firstOfMonth . "&commercial_acct=false";
        
        $this->logger->info('Scraping recreation.gov');
        $scrapedData = $webScrapingClient->scrapeJson($recreationDotGovUrl);
        $this->logger->info('Done scraping');

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
                $this->logger->info('No EntryPointAvailability exists for this date and ep. Creating one now.');

                $entryPointAvailability = new EntryPointAvailability();
                
                $entryPointAvailability->setEntryPoint($entryPoint);
                $entryPointAvailability->setMonitoredDate($monitoredDate);
                $entryPointAvailability->setAvailableCount($availablePermitCount);

                $this->entityManager->persist($entryPointAvailability);
            }

            if($entryPointAvailability->getAvailableCount() !== $availablePermitCount) {
                if(
                    $entryPointAvailability->getAvailableCount() === 0
                    && $availablePermitCount > 0
                ) {
                    $this->logger->info('Permits now available at ep ' . $entryPoint->getName() . ' on ' . $monitoredDate->getDate()->format('Y-m-d'));
                    $this->logger->info('There were ' . $entryPointAvailability->getAvailableCount() . ', ' . 'now there are ' . $availablePermitCount);
                    
                    //send alert
                    $this->sendAlertEmail->sendMonitoredDateAlert($monitoredDate, $entryPoint);
                }
                
                $entryPointAvailability->setAvailableCount($availablePermitCount);
                $this->entityManager->persist($entryPointAvailability);
            }
        }

        $this->logger->info('Done checking entry points for this message');
        $this->entityManager->flush();
    }

    private function getAvailablePermitCount(
        int $divisionId, 
        array $scrapedData,
        string $formattedTargetDate
    ): int
    {
        $dateAvailability = $scrapedData['payload']['availability'][$divisionId]['date_availability'][$formattedTargetDate];

        if ($dateAvailability === null) {
            $this->logger->warning('No availability data found for entry point division id ' . $divisionId . ' on ' . $formattedTargetDate);
            return 0;
        }

        return $dateAvailability['remaining'];
    }
}
