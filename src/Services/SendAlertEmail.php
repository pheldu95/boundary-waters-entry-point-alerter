<?php

namespace App\Services;

use App\Entity\EntryPoint;
use App\Entity\MonitoredDate;
use App\Entity\PermitWatch;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;

class SendAlertEmail
{
    public function __construct(
        private MailerInterface $mailer,
        private ?LoggerInterface $logger = null
    ) {}

    /**
     * Send a permit alert email.
     * 
     * @param PermitWatch  $permitWatch
     *
     * @return bool
     */
    public function sendPermitAlert(PermitWatch $permitWatch): bool
    {
        $emailAddress = $permitWatch->getUser()->getEmail();

        $this->logger->info('Sending permit alert email to ' . $emailAddress, [
            'permitWatchId' => $permitWatch->getId(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $email = (new TemplatedEmail())
            ->from(new Address('alert@entry-point-alerter.com', 'Entry Point Alerter'))
            ->to($emailAddress)
            ->subject($permitWatch->getEntryPoint()->getName() . ' Permit Available!')
            ->htmlTemplate('/email/permit_alert.html.twig')
            ->textTemplate('/email/permit_alert.txt.twig')
            ->context([
                'entryPointName' => $permitWatch->getEntryPoint()->getName(),
                'targetDate' => $permitWatch->getTargetDate()->format('Y-m-d')
            ]);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            if ($this->logger) {
                $this->logger->error('Failed to send permit alert email: ' . $e->getMessage());
            }
            return false;
        }

        $this->logger->info('Sent permit alert email to ' . $emailAddress, [
            'permitWatchId' => $permitWatch->getId(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        return true;
    }

    /**
     * Send a monitored date alert email.
     * 
     * @param MonitoredDate  $permitWatch
     *
     * @return bool
     */
    public function sendMonitoredDateAlert(MonitoredDate $monitoredDate, EntryPoint $entryPoint): bool
    {
        $emailAddress = $monitoredDate->getUser()->getEmail();

        $this->logger->info('Sending monitored date alert email to ' . $emailAddress, [
            'MonitoredDate id' => $monitoredDate->getId(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $email = (new TemplatedEmail())
            ->from(new Address('phelps.duncan@gmail.com', 'Entry Point Alerter'))
            ->to($emailAddress)
            ->subject($entryPoint->getName() . ' Permit Available!')
            ->htmlTemplate('/email/monitored_date_alert.html.twig')
            ->textTemplate('/email/monitored_date_alert.txt.twig')
            ->context([
                'entryPointName' => $entryPoint->getName(),
                'date' => $monitoredDate->getDate()->format('Y-m-d')
            ]);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            if ($this->logger) {
                $this->logger->error('Failed to send monitored date alert email: ' . $e->getMessage());
            }
            return false;
        }

        $this->logger->info('Sent monitored date alert email to ' . $emailAddress, [
            'MonitoredDate id' => $monitoredDate->getId(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        return true;
    }
}
