<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Queue;

use function array_key_exists;

/**
 * Class MessageAttributes
 */
class MessageAttributes
{
    /**
     * @var array
     */
    private $attributes;

    /**
     * MessageAttributes constructor.
     */
    public function __construct()
    {
        $this->attributes = [];
    }

    /**
     * @param string $attributeName
     *
     * @return $this
     */
    public function remove(string $attributeName): self
    {
        if (array_key_exists($attributeName, $this->attributes)) {
            unset($this->attributes[$attributeName]);
        }

        return $this;
    }

    /**
     * @param string $attributeName
     * @param string $attributeValue
     *
     * @return $this
     */
    public function addString(string $attributeName, string $attributeValue): self
    {
        return $this->addAttribute($attributeName, $attributeValue, 'String');
    }

    /**
     * @param string $attributeName
     * @param string $attributeValue
     *
     * @return $this
     */
    public function addNumber(string $attributeName, string $attributeValue): self
    {
        return $this->addAttribute($attributeName, $attributeValue, 'Number');
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * @param string $attributeName
     * @param string $attributeValue
     * @param string $dataType
     *
     * @return $this
     */
    private function addAttribute(string $attributeName, string $attributeValue, string $dataType): self
    {
        $this->attributes[$attributeName] = [
            'DataType'    => $dataType,
            'StringValue' => $attributeValue,
        ];

        return $this;
    }
}
