<?php

namespace Tvup\ElOverblikApi;

interface ElOverblikApiInterface
{
    public function token(string $token): void;

    public function getFirstMeteringPoint(string $refresh_token): string;

    public function getMeteringPointData(): array;

    public function getHourTimeSeriesFromMeterData(string $fromDate, string $toDate, ?string $meteringPointId): array;

    public function getCharges(string $meteringPointId): array;

    public function setDebug(bool $debug): void;
}