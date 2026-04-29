<?php

declare(strict_types=1);

namespace DiveShop365\Lighthouse\Model;

use SilverStripe\ORM\DataObject;

class LighthouseScore extends DataObject
{
    private static string $table_name = 'LighthouseScore';

    private static array $db = [
        'PageClass' => 'Varchar(255)',
        'URL' => 'Varchar(500)',
        'Strategy' => 'Varchar(10)',
        'Performance' => 'Int',
        'Accessibility' => 'Int',
        'BestPractices' => 'Int',
        'SEO' => 'Int',
        'FCP' => 'Varchar(20)',
        'LCP' => 'Varchar(20)',
        'CLS' => 'Varchar(20)',
        'TBT' => 'Varchar(20)',
        'SpeedIndex' => 'Varchar(20)',
        'RawJSON' => 'Text',
        'ScannedAt' => 'Datetime',
    ];

    private static array $has_one = [
        'Page' => DataObject::class,
    ];

    private static string $default_sort = 'ScannedAt DESC';

    private static array $summary_fields = [
        'ScannedAt' => 'Scanned',
        'Strategy' => 'Strategy',
        'Performance' => 'Performance',
        'Accessibility' => 'Accessibility',
        'BestPractices' => 'Best Practices',
        'SEO' => 'SEO',
    ];
}
