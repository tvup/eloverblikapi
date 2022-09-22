<?php

namespace Tvup\ElOverblikApi;

interface ElOverblikApiInterface
{
    public function token(string $token): void;

    public function getFirstMeteringPoint(): string;

    public function getMeteringPointData(): string;

    public function getHourTimeSeriesFromMeterData(string $fromDate, string $toDate, ?string $meteringPointId): array;

    public function getCharges(string $meteringPointId): array;

    public function setDebug(bool $debug): void;
}