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
        $array =  unserialize($file);
        $days = array();
        foreach ($array as $day) {
            $day_key = Carbon::parse($day['timeInterval']['start'])->setTimezone('Europe/Copenhagen')->toDateString();
            $hourArray = array();
            foreach ($day['Point'] as $point) {
                array_push($hourArray, $point['out_Quantity.quantity']);
            }
            $days[$day_key] = $hourArray;
        }
        return $days;
    }

    public function setDebug(bool $debug): void
    {
        // TODO: Implement setDebug() method.
    }
}