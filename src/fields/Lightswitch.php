<?php

namespace needletail\needletail\fields;

class Lightswitch extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Lightswitch';
    public static $class = 'craft\fields\Lightswitch';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'needletail/_includes/fields/lightswitch';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $value = $this->element->getFieldValue($this->fieldHandle);

        if ( $value === NULL || $value === FALSE)
            return $value;

        return !!($value);
    }
}
