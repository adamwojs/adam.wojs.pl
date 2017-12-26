<?php

namespace AppBundle\FieldType\CodeSnippet;

use eZ\Publish\Core\FieldType\Value as BaseValue;

class Value extends BaseValue
{
    /**
     * @var string Language name
     */
    public $language;

    /**
     * @var string Code snippet
     */
    public $contents;

    /**
     * @var int Number of first line
     */
    public $firstLine = 1;

    /**
     * @var string The lines to be highlighted
     */
    public $highlightedLines;

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return (string) $this->contents;
    }

    public static function fromString($contents)
    {
        return new Value([
            'contents' => $contents
        ]);
    }
}
