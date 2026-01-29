<?php

namespace needletail\needletail\fields;

class Number extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Number';
    public static $class = 'craft\fields\Number';


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

        if ( $value === NULL || $value === FALSE)
            return $value;

        /** @var \craft\fields\Number $field */
        $field = $this->field;

        if ( $decimals = $field->decimals )
            return number_format($value, $decimals, '.', '');

        return floatval($value);
    }

}
