<?php

namespace Tvup\ElOverblikApi;

class ElOverblikApiMock implements ElOverblikApiInterface
{

    public function login(string $email, string $password)
    {
        // TODO: Implement login() method.
    }

    public function getConsumptionData(string $fileType, string $installationNumber, int $consumerNumber, int $meterId, int $counterId, int $type, int $utility, string $unit, string $factoryNumber): array
    {
        $data = El::CONSUMPTION_DATA;

        $dataArray = explode(PHP_EOL, $data);
        array_shift($dataArray); //First line is "sep=" for some reason
        array_shift($dataArray); //Second line is table headers

        $returnArray = array();

        foreach ($dataArray as $line) {
            $lineArray = explode(';', $line);
            array_push($returnArray, [$lineArray[0] => $lineArray[1]]);
        }

        return $returnArray;
    }
}