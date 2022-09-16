<?php

namespace Tvup\ElOverblikApi;

use Carbon\Carbon;

class ElOverblikApiMock implements ElOverblikApiInterface
{

    public function token(string $token): void
    {
        // TODO: Implement token() method.
    }

    public function getFirstMeteringPoint(): string
    {
        return 'fisk';
    }

    public function getHourTimeSeriesFromMeterData(string $fromDate, string $toDate, ?string $meteringPointId): array
    {
        $file = file_get_contents(__DIR__ . '/../example_data/el.dat');
        $result =  unserialize($file);
        $allHours = array();
        foreach ($result as $day) {
            $day_key = Carbon::parse($day['timeInterval']['start'])->setTimezone('Europe/Copenhagen')->startOfDay();
            foreach ($day['Point'] as $point) {
                $day_hour_key = $day_key->toDateTimeLocalString();
                $allHours[$day_hour_key] = $point['out_Quantity.quantity'];
                $day_key->addHour();
            }
        }
        return $allHours;
    }

    public function setDebug(bool $debug): void
    {
        // TODO: Implement setDebug() method.
    }
}