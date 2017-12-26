<?php

namespace AppBundle\FieldType\CodeSnippet;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\Value as CoreValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;

class Type extends FieldType
{
    const FIELD_TYPE_IDENTIFIER = 'codesnippet';

    /**
     * @inheritdoc
     */
    public function getFieldTypeIdentifier()
    {
        return self::FIELD_TYPE_IDENTIFIER;
    }

    /**
     * @inheritdoc
     */
    public function getName(SPIValue $value)
    {
        return $value->contents;
    }

    /**
     * @inheritdoc
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * @inheritdoc
     */
    public function fromHash($hash)
    {
        if ($hash === null) {
            return $this->getEmptyValue();
        }

        return new Value($hash);
    }

    /**
     * @inheritdoc
     */
    public function toHash(SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        return [
            'language' => $value->language,
            'contents' => $value->contents,
            'firstLine' => $value->firstLine,
            'highlightedLines' => $value->highlightedLines
        ];
    }

    /**
     * @inheritdoc
     */
    protected function createValueFromInput($inputValue)
    {
        switch (true) {
            case is_string($inputValue):
                return Value::fromString($inputValue);
            case is_array($inputValue):
                return new Value($inputValue);
            default:
                return $inputValue;
        }
    }

    /**
     * @inheritdoc
     */
    protected function checkValueStructure(CoreValue $value)
    {
        if (!is_string($value->contents)) {
            throw new InvalidArgumentType(
                '$value->contents',
                'string',
                $value->text
            );
        }
    }

    /**
     * @inheritdoc
     */
    protected function getSortInfo(CoreValue $value)
    {
        return false;
    }
}
