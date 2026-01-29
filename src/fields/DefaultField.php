<?php

namespace needletail\needletail\fields;

use craft\fields\data\ColorData;
use craft\fields\data\MultiOptionsFieldData;
use craft\fields\data\OptionData;
use craft\fields\data\SingleOptionFieldData;

class DefaultField extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Default';
    public static $class = 'craft\fields\Default';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'needletail/_includes/fields/default';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $value = $this->element->getFieldValue($this->fieldHandle);

        if ( is_null($value) )
            return null;

        if ( is_string($value) )
            return $value;


        $stringableInstances = [
            ColorData::class,
        ];

        foreach ( $stringableInstances as $stringableInstace ) {
            if ( $value instanceof $stringableInstace )
                return (string) $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string)$value;
        }


        if ($value instanceof MultiOptionsFieldData) {
            return array_map(function ($item) {
                if ($item instanceof OptionData) {
                    return $item->label;
                }
                return $item;
            }, (array)$value);
        }

        if ($value instanceof SingleOptionFieldData) {
            return $value->label;
        }

        return $value;
    }
}