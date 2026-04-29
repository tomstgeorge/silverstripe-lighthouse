<?php

declare(strict_types=1);

namespace DiveShop365\Lighthouse\Job;

use DiveShop365\Lighthouse\Service\PageSpeedService;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataObject;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJobService;

class LighthouseJob extends AbstractQueuedJob
{
    public function __construct(int $recordID = 0, string $strategy = 'mobile', string $className = SiteTree::class)
    {
        if ($recordID) {
            $this->RecordID = $recordID;
            $this->Strategy = $strategy;
            $this->RecordClass = $className;
        }
    }

    public function getTitle(): string
    {
        return 'Lighthouse scan (' . $this->RecordClass . ' #' . $this->RecordID . ', ' . $this->Strategy . ')';
    }

    public function process(): void
    {
        $record = DataObject::get_by_id($this->RecordClass, $this->RecordID);
        if (!$record) {
            $this->addMessage('Record not found');
            $this->isComplete = true;
            return;
        }

        $service = Injector::inst()->get(PageSpeedService::class);
        $service->fetchAndStore($record, $this->Strategy);

        $this->isComplete = true;
    }

    public static function queue(int $recordID, string $strategy = 'mobile', string $className = SiteTree::class): void
    {
        $job = new self($recordID, $strategy, $className);
        Injector::inst()->get(QueuedJobService::class)->queueJob($job);
    }
}
