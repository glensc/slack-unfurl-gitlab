<?php

namespace GitlabSlackUnfurl\Test;

use GitlabSlackUnfurl\Traits\SanitizeTextTrait;

class SanitizeTextTest extends TestCase
{
    /**
     * @var SanitizeTextTrait
     */
    private $sanitize;

    public function setUp(): void
    {
        $this->sanitize = new class {
            use SanitizeTextTrait;

            public function sanitize(string $input): string
            {
                return $this->sanitizeText($input);
            }
        };
    }

    /**
     * @dataProvider sanitizeData
     */
    public function testStripHtml(string $input, string $expected)
    {
        $result = $this->sanitize->sanitize($input);
        $this->assertEquals($expected, $result);
    }

    public function sanitizeData()
    {
        yield [
            $this->readFile('text.input.md'),
            $this->readFile('text.expected.md'),
        ];
    }

    private function readFile($name)
    {
        $fileName = __DIR__ . '/Resources/' . $name;
        $contents = file_get_contents($fileName);

        return trim($contents);
    }
}
