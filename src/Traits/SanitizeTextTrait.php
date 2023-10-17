<?php

namespace GitlabSlackUnfurl\Traits;

trait SanitizeTextTrait
{
    protected function sanitizeText(?string $text): string
    {
        $text = str_replace("\r\n", "\n", $text);
        // remove html comments
        $text = preg_replace("/<!--.*?-->/s", "", $text);
        $text = trim($text);

        return $text;
    }
}
