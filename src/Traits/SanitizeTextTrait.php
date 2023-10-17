<?php

namespace GitlabSlackUnfurl\Traits;

trait SanitizeTextTrait
{
    protected function sanitizeText(?string $text): string
    {
        $text = str_replace("\r\n", "\n", $text);
        $text = trim($text);

        return $text;
    }
}
