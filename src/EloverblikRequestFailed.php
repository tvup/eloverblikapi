<?php

namespace Tvup\ElOverblikApi;

class EloverblikRequestFailed
{
    private string $verb;
    private string $endpoint;
    private int $code;

    /**
     * @param string $verb
     * @param string $endpoint
     */
    public function __construct(string $verb, string $endpoint, int $code)
    {
        $this->verb = $verb;
        $this->endpoint = $endpoint;
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getVerb(): string
    {
        return $this->verb;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }




}