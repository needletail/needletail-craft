<?php

namespace needletail\needletail\base;

use craft\base\Component;

abstract class Element extends Component
{
    public $element;

    public $bucket;

    // Public Methods
    // =========================================================================

    public function getName()
    {
        return $this::$name;
    }

    public function getClass()
    {
        return get_class($this);
    }

    public function getElementClass()
    {
        return $this::$class;
    }

    public function parseAttribute(\craft\base\ElementInterface $element, $handle, $data)
    {
        // Find the class to deal with the attribute
        $name = 'parse' . ucwords($handle);

        // Set a default handler for non-specific attribute classes
        if (!method_exists($this, $name)) {
            return $element->{$handle};
        }

        $parsedValue = $this->$name($element, $data);

        return $parsedValue;
    }

    public function parseId($element, $data)
    {
        return (int) $element->id;
    }

    public function parseAuthorId($element, $data)
    {
        return $this->parseAnInteger($element->authorId);
    }

    public function parsePostDate($element, $data)
    {
        return $this->parseADateTimeValue($element->postDate);
    }

    public function parseExpiryDate($element, $data)
    {
        return $this->parseADateTimeValue($element->expiryDate);
    }

    public function parseDateCreated($element, $data)
    {
        return $this->parseADateTimeValue($element->dateCreated);
    }

    public function parseDateModified($element, $data)
    {
        return $this->parseADateTimeValue($element->dateModified);
    }

    public function parseADateTimeValue (\DateTime $date = null) {
        if ( ! $date )
            return $date;

        return $date->format(\DateTime::ATOM);
    }

    public function parseAnInteger($string)
    {
        return (int) $string;
    }
}
