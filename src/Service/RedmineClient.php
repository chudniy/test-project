<?php

namespace App\Service;

use Redmine;


class RedmineClient
{
    private $apiUrl;
    private $apiKey;

    public function __construct($apiUrl, $apiKey)
    {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
    }

    public function getClient()
    {
        $client = new Redmine\Client($this->apiUrl, $this->apiKey);

        return $client;
    }
}