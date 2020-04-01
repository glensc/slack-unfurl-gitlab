<?php

namespace GitlabSlackUnfurl\Test;

use Generator;
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
        // create single object to compare datasets easier
        $parts['url'] = $match[1]['url'] = $url;
        $parts['route'] = $route;
        $match[1]['route'] = $match[0];
        $this->assertEquals($parts, $match[1]);
    }

    public function routesProvider(): Generator
    {
        foreach ($this->loadYaml('routes.yml') as $item) {
            yield $item[0] => $item;
        }
    }
}
