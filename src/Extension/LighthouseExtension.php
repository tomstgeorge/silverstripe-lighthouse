<?php

declare(strict_types=1);

namespace DiveShop365\Lighthouse\Extension;

use DiveShop365\Lighthouse\Job\LighthouseJob;
use DiveShop365\Lighthouse\Model\LighthouseScore;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;
use SilverStripe\Forms\LiteralField;

class LighthouseExtension extends Extension
{
    private static array $has_many = [
        'LighthouseScores' => LighthouseScore::class . '.Page',
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        $fields->removeByName('LighthouseScores');

        $latest = $this->getOwner()->LighthouseScores()->first();

        $content = '';
        if ($latest) {
            $content = $this->renderScoreCard($latest);
        } else {
            $content = '<p class="message info">No Lighthouse scores yet. Publish this page to trigger a scan.</p>';
        }

        $fields->addFieldsToTab('Root.Lighthouse', [
            LiteralField::create('LighthouseLatest', $content),
            GridField::create(
                'LighthouseScores',
                'Score History',
                $this->getOwner()->LighthouseScores(),
                GridFieldConfig_RecordViewer::create()
            ),
        ]);
    }

    public function onAfterPublish(): void
    {
        $this->queueScan();
    }

    public function onAfterPublishRecursive($original): void
    {
        $this->queueScan();
    }

    public function onAfterWrite(): void
    {
        // For non-versioned DataObjects (e.g. GroupTrip) trigger on save
        $owner = $this->getOwner();
        if (!$owner->hasExtension(\SilverStripe\Versioned\Versioned::class)) {
            $this->queueScan();
        }
    }

    private function queueScan(): void
    {
        LighthouseJob::queue($this->getOwner()->ID, 'mobile', get_class($this->getOwner()));
    }

    private function renderScoreCard(LighthouseScore $score): string
    {
        return sprintf(
            '<div style="padding:10px;margin-bottom:15px;background:#f7f7f7;border:1px solid #ddd;border-radius:4px">
                <h4 style="margin-top:0">Latest Scan: %s (%s)</h4>
                <table style="width:100%%;border-collapse:collapse">
                    <tr><th style="text-align:left;padding:4px">Performance</th><td style="padding:4px">%s</td>
                        <th style="text-align:left;padding:4px">Accessibility</th><td style="padding:4px">%s</td></tr>
                    <tr><th style="text-align:left;padding:4px">Best Practices</th><td style="padding:4px">%s</td>
                        <th style="text-align:left;padding:4px">SEO</th><td style="padding:4px">%s</td></tr>
                </table>
                <hr style="margin:8px 0">
                <small>FCP: %s | LCP: %s | CLS: %s | TBT: %s | Speed Index: %s</small>
            </div>',
            $score->ScannedAt,
            $score->Strategy,
            $this->badge($score->Performance),
            $this->badge($score->Accessibility),
            $this->badge($score->BestPractices),
            $this->badge($score->SEO),
            $score->FCP,
            $score->LCP,
            $score->CLS,
            $score->TBT,
            $score->SpeedIndex
        );
    }

    private function badge(int $value): string
    {
        $color = match (true) {
            $value >= 90 => '#0cce6b',
            $value >= 50 => '#ffa400',
            default => '#ff4e42',
        };
        return sprintf('<span style="display:inline-block;width:36px;height:36px;line-height:36px;text-align:center;border-radius:50%%;background:%s;color:#fff;font-weight:bold">%d</span>', $color, $value);
    }
}
