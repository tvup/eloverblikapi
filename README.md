# energioverblikapi
This repository contains PHP library code to facilitate communication with Eloverblik Api's. It is intended to be loaded into another PHP application as a composer package through packagist.
The API exposes functions for getting token, meteringpoints, and for getting consumption data..

**Use at own risk**


## Prerequisites
Requires a refresh token obtained at https://eloverblik.dk/customer/login

## Installation with composer
```
composer require tvup/eloverblikapi
```


### Example code
```
require_once 'vendor/autoload.php';

$eloverblik = new Tvup\ElOverblikApi\ElOverblikApi();
//$eloverblik->setDebug(true);

$eloverblik->token('YOUR_REFRESH_TOKEN');

$firstMeteringPoint = $eloverblik->getFirstMeteringPoint();
$response = $eloverblik->getHourTimeSeriesFromMeterData('2022-01-01', '2022-01-31');

print_r($response);
```

## Feedback
Always welcome. This project was created because I was curious if it was possible, and I tend to lose interest when the goal has been accomplished.
You wan't something differently - you can make a pull-request we can share a bit of each other's understanding.

## Appreciate it?
Great. Glad I could help.
### I want to make a donation as a sign of appreciation
Don't
#### I insist
It will probably be spent on beer: https://www.buymeacoffee.com/tvup
