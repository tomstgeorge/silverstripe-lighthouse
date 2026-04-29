<?php

declare(strict_types=1);

namespace DiveShop365\Lighthouse\Extension;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;

class LighthouseSiteConfigExtension extends Extension
{
    private static array $db = [
        'LighthouseApiKey' => 'Varchar(255)',
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        $fields->addFieldToTab(
            'Root.Lighthouse',
            TextField::create('LighthouseApiKey', 'Google PageSpeed Insights API Key')
                ->setDescription('Get a key from <a href="https://developers.google.com/speed/docs/insights/v5/get-started" target="_blank">Google PSI docs</a>')
        );
    }
}
