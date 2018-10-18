<?php

namespace GitlabSlackUnfurl\Test;

use GuzzleHttp\Psr7\Response;

class MockJsonResponse extends Response
{
    public function __construct(string $fileName)
    {
        $body = file_get_contents(__DIR__ . '/Resources/' . $fileName);
        parent::__construct(200, ['Content-Type' => 'application/json'], $body);
    }
}