<?php

namespace needletail\needletail\fields;

use Cake\Utility\Hash;

class RadioButtons extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'RadioButtons';
    public static $class = 'craft\fields\RadioButtons';


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
        return $value->label;
    }

}
