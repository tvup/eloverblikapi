<?php

namespace Tvup\ElOverblikApi;

class EloverblikRequestFailed
{
    private string $verb;
    private string $endpoint;

    /**
     * @param string $verb
     * @param string $endpoint
     */
    public function __construct(string $verb, string $endpoint)
    {
        $this->verb = $verb;
        $this->endpoint = $endpoint;
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


}