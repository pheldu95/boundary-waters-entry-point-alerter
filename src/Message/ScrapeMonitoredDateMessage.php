<?php
namespace App\Message;

use App\Entity\MonitoredDate;

class ScrapeMonitoredDateMessage
{
    public function __construct(
        private MonitoredDate $monitoredDate
    ) {
    }

    public function getMonitoredDate(): MonitoredDate
    {
        return $this->monitoredDate;
    }
}