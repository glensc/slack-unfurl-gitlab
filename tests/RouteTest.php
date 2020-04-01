<?php

namespace GitlabSlackUnfurl\Test;

use GitlabSlackUnfurl\Route\GitLabRoutes;

class RouteTest extends TestCase
{
    use YamlTrait;

    /**
     * @param string $url
     * @param string $route
     * @param array $parts
     * @dataProvider routesProvider
     */
    public function testRoutes(string $url, string $route, array $parts): void
    {
        $router = new GitLabRoutes($this->domain);
        $match = $router->match($url);
        $this->assertEquals($route, $match[0]);
        $this->assertEquals($parts, $match[1]);
    }

    public function routesProvider(): array
    {
        return $this->loadYaml('routes.yml');
    }
}
