<?php

namespace Ispahbod\Connect;

use GuzzleHttp\Client;
use Ispahbod\Connect\Method\Getter;
use Ispahbod\Connect\Method\Request;
use Ispahbod\Connect\Method\Setter;

class Connect
{
    use Setter;
    use Getter;
    use Request;
    protected Client $client;
    public function __construct()
    {
        $this->client = new Client();
    }
}