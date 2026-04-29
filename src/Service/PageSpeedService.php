<?php

declare(strict_types=1);

namespace DiveShop365\Lighthouse\Service;

use DiveShop365\Lighthouse\Model\LighthouseScore;
use GuzzleHttp\Client;
use SilverStripe\Control\Director;
use SilverStripe\ORM\DataObject;
use SilverStripe\SiteConfig\SiteConfig;

class PageSpeedService
{
    private const API_URL = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

    public function fetchAndStore(DataObject $record, string $strategy = 'mobile'): ?LighthouseScore
    {
        $apiKey = SiteConfig::current_site_config()->LighthouseApiKey;
        if (!$apiKey) {
            return null;
        }

        $url = $this->resolveUrl($record);
        if (!$url) {
            return null;
        }

        $client = new Client(['timeout' => 120]);
        $response = $client->get(self::API_URL, [
            'query' => [
                'url' => $url,
                'key' => $apiKey,
                'strategy' => $strategy,
                'category' => ['performance', 'accessibility', 'best-practices', 'seo'],
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true);
        $categories = $data['lighthouseResult']['categories'] ?? [];
        $audits = $data['lighthouseResult']['audits'] ?? [];

        $score = LighthouseScore::create();
        $score->PageID = $record->ID;
        $score->PageClass = get_class($record);
        $score->URL = $url;
        $score->Strategy = $strategy;
        $score->Performance = (int) round(($categories['performance']['score'] ?? 0) * 100);
        $score->Accessibility = (int) round(($categories['accessibility']['score'] ?? 0) * 100);
        $score->BestPractices = (int) round(($categories['best-practices']['score'] ?? 0) * 100);
        $score->SEO = (int) round(($categories['seo']['score'] ?? 0) * 100);
        $score->FCP = $audits['first-contentful-paint']['displayValue'] ?? '';
        $score->LCP = $audits['largest-contentful-paint']['displayValue'] ?? '';
        $score->CLS = $audits['cumulative-layout-shift']['displayValue'] ?? '';
        $score->TBT = $audits['total-blocking-time']['displayValue'] ?? '';
        $score->SpeedIndex = $audits['speed-index']['displayValue'] ?? '';
        $score->RawJSON = json_encode($data);
        $score->ScannedAt = date('Y-m-d H:i:s');
        $score->write();

        return $score;
    }

    private function resolveUrl(DataObject $record): ?string
    {
        if ($record->hasMethod('AbsoluteLink')) {
            return $record->AbsoluteLink();
        }
        if ($record->hasMethod('Link')) {
            return Director::absoluteURL($record->Link());
        }
        return null;
    }
}
