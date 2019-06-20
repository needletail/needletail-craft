<?php

namespace needletail\needletail\fields;

use craft\helpers\Localization;

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

        if ( $decimals = $this->field->decimals )
            return number_format($value, $decimals, '.', '');

        return $value;
    }

}
