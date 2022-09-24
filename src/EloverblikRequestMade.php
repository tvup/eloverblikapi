<?php

namespace Tvup\ElOverblikApi;

class EloverblikRequestMade
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
}