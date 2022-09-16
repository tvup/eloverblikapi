<?php

namespace Tvup\ElOverblikApi;

class ElOverblikApiDataAccessToken
{
    private $dataAccessToken;
    private $issued_at;

    /**
     * @param $dataAccessToken
     * @param $issued_at
     */
    public function __construct($dataAccessToken, $issued_at)
    {
        $this->dataAccessToken = $dataAccessToken;
        $this->issued_at = $issued_at;
    }


    /**
     * @return mixed
     */
    public function getDataAccessToken()
    {
        return $this->dataAccessToken;
    }

    /**
     * @param mixed $dataAccessToken
     */
    public function setDataAccessToken($dataAccessToken): void
    {
        $this->dataAccessToken = $dataAccessToken;
    }

    /**
     * @return mixed
     */
    public function getIssuedAt()
    {
        return $this->issued_at;
    }

    /**
     * @param mixed $issued_at
     */
    public function setIssuedAt($issued_at): void
    {
        $this->issued_at = $issued_at;
    }


}