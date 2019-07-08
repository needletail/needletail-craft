<?php

namespace needletail\needletail\fields;

class Dropdown extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Dropdown';
    public static $class = 'craft\fields\Dropdown';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'needletail/_includes/fields/option-select';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $value = $this->element->getFieldValue($this->fieldHandle);

        return $value ? $value->label : null;
    }

}
