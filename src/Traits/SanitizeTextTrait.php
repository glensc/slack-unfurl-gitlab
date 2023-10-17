<?php

namespace GitlabSlackUnfurl\Traits;

use function GitlabSlackUnfurl\sanitizeText;

trait SanitizeTextTrait
{
    /**
     * @deprecated
     */
    protected function sanitizeText(?string $text): string
    {
        return sanitizeText($text);
    }
}
