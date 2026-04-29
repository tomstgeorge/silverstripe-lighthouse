# Silverstripe Lighthouse

Automatically fetches Google PageSpeed Insights (Lighthouse) scores when a page is published and displays results on a "Lighthouse" tab in the CMS.

## Installation

```sh
composer require diveshop365/silverstripe-lighthouse
```

## Configuration

1. Get a free API key from [Google PageSpeed Insights](https://developers.google.com/speed/docs/insights/v5/get-started)
2. In the CMS, go to **Settings → Lighthouse** and paste your API key
3. Publish any page — a queued job will fetch the scores

## How it works

- On `onAfterPublish`, a `LighthouseJob` is queued via `symbiote/silverstripe-queuedjobs`
- The job calls the PSI API with the page's absolute URL
- Scores are stored in a `LighthouseScore` DataObject linked to the page
- The "Lighthouse" tab shows the latest scores with colour-coded badges and a history grid

## Extending to other DataObjects

Add the extension to any class that has an `AbsoluteLink()` method:

```yaml
DiveShop365\GroupTrips\Model\GroupTrip:
  extensions:
    - DiveShop365\Lighthouse\Extension\LighthouseExtension
```

## Requirements

- PHP 8.3+
- Silverstripe 6
- `symbiote/silverstripe-queuedjobs` ^6.0
- `guzzlehttp/guzzle` ^7.0
