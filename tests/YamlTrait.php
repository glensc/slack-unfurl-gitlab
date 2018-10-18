<?php

namespace GitlabSlackUnfurl\Test;

use Symfony\Component\Yaml\Yaml;

trait YamlTrait
{
    protected function loadYaml(string $name)
    {
        $fileName = $this->getDataProviderFilename($name);
        $contents = file_get_contents($fileName);

        return Yaml::parse($contents);
    }

    protected function dumpYaml(string $name, array $data)
    {
        $fileName = $this->getDataProviderFilename($name);

        $flags = Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK;
        $contents = Yaml::dump($data, $inline = 5, $indent = 4, $flags);
        file_put_contents($fileName, $contents);
    }

    private function getDataProviderFilename($name): string
    {
        return __DIR__ . '/Resources/DataProvider/' . $name;
    }
}