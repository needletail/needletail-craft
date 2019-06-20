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

    public function parsePostDate($element, $data)
    {
        return $this->parseADateTimeValue($element->postDate);
    }

    public function parseExpiryDate($element, $data)
    {
        return $this->parseADateTimeValue($element->expiryDate);
    }

    public function parseADateTimeValue (\DateTime $date = null) {
        if ( ! $date )
            return $date;

        return $date->format('Y-m-d H:i:s');
    }
}