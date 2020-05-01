<?php

namespace GitlabSlackUnfurl\Test;

use GitlabSlackUnfurl\Route;
use GitlabSlackUnfurl\Route\GitLabRoutes;
use GitlabSlackUnfurl\Traits\SanitizeTextTrait;

class UnfurlTest extends TestCase
{
    use YamlTrait;
    use SanitizeTextTrait;

    /**
     * @param string $url
     * @param array $parts
     * @param array $expected
     * @param array $responses
     * @dataProvider issueDataProvider
     */
    public function testIssueUnfurl(string $url, array $parts, array $expected, array $responses): void
    {
        $router = new GitLabRoutes($this->domain);
        $match = $router->match($url)[1];
        $this->assertEquals($parts, $match);

        /** @var Route\Issue $unfurler */
        $unfurler = $this->getRouteHandler(Route\Issue::class, $responses, $history);

        $result = $unfurler->unfurl($url, $parts);
        $this->assertCount(count($responses), $history);
        $this->assertEquals($expected, $result);
    }

    /**
     * @param string $url
     * @param array $parts
     * @param array $expected
     * @param array $responses
     * @dataProvider mergeDataProvider
     */
    public function testMergeRequest(string $url, array $parts, array $expected, array $responses): void
    {
        $router = new GitLabRoutes($this->domain);
        $match = $router->match($url)[1];
        $this->assertEquals($parts, $match);

        /** @var Route\MergeRequest $unfurler */
        $unfurler = $this->getRouteHandler(Route\MergeRequest::class, $responses, $history);

        $result = $unfurler->unfurl($url, $parts);
        $this->assertCount(count($responses), $history);
        $this->assertEquals($expected, $result);
    }

    /**
     * @param string $url
     * @param array $parts
     * @param array $expected
     * @param array $responses
     * @dataProvider noteDataProvider
     */
    public function testNote(string $url, array $parts, array $expected, array $responses): void
    {
        $router = new GitLabRoutes($this->domain);
        $match = $router->match($url)[1];
        $this->assertEquals($parts, $match);

        /** @var Route\Note $unfurler */
        $unfurler = $this->getRouteHandler(Route\Note::class, $responses, $history);

        $result = $unfurler->unfurl($url, $parts);
        $this->assertCount(count($responses), $history);
        $this->assertEquals($expected, $result);
    }

    private function loadDataProvider(string $name)
    {
        $data = $this->loadYaml($name);
        foreach ($data as &$arguments) {
            if (isset($arguments[2]['text'])) {
                $arguments[2]['text'] = $this->sanitizeText($arguments[2]['text']);
            }
            $responses = [];
            foreach ($arguments[3] as $fileName) {
                $responses[] = new MockJsonResponse($fileName);
            }
            $arguments[3] = $responses;
        }

        return $data;
    }

    public function issueDataProvider(): array
    {
        return $this->loadDataProvider('issues.yml');
    }

    public function mergeDataProvider(): array
    {
        return $this->loadDataProvider('merge_requests.yml');
    }

    public function noteDataProvider(): array
    {
        return $this->loadDataProvider('notes.yml');
    }
}
