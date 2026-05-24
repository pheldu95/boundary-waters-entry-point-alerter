<?php
namespace App\Message;

use App\Entity\MonitoredDate;

class ScrapeMonitoredDateMessage
{
    public function __construct(
        public readonly int $monitoredDateId
    ) {}
}