<?php

namespace GitlabSlackUnfurl\Test;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected $url;
    protected $domain;

    public function setUp()
    {
        $this->url = 'https://gitlab.com';
        $this->domain = parse_url($this->url, PHP_URL_HOST);
    }
}
