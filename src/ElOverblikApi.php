<?php

namespace Tvup\ElOverblikApi;

use Carbon\Carbon;

class ElOverblikApi extends ElOverblikApiBase implements ElOverblikApiInterface
{
    protected bool $debug;

    public function token(string $refreshToken): void
    {
        if ('' == $refreshToken) {
            throw new ElOverblikApiException(['Refresh token cannot be blank'], [], '1');
        }
        $this->setRefreshToken($refreshToken);
        $this->makeErrorHandledRequest('GET', 'token', null, null);
    }

    public function getFirstMeteringPoint(): string
    {
        $json = $this->makeErrorHandledRequest('GET', 'meteringpoints/meteringpoints', null, null, true);
        $meteringPointId = json_decode($json, true)['result'][0]['meteringPointId'];
        $this->meteringPointId = $meteringPointId;
        return $meteringPointId;
    }

    public function getHourTimeSeriesFromMeterData(string $fromDate, string $toDate, ?string $meteringPointId): array
    {
        $meteringPointId = $meteringPointId ? : $this->meteringPointId;
        $payload = ['meteringPoints' => ['meteringPoint'=> [$meteringPointId]]];
        $json = $this->makeErrorHandledRequest('POST', 'meterdata/gettimeseries/' . $fromDate . '/' . $toDate . '/Hour', null, $payload, true);
        $json = json_decode($json, true);
        $result = $json['result'][0]['MyEnergyData_MarketDocument']['TimeSeries'][0]['Period'];
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

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }


}