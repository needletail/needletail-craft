<?php
namespace needletail\needletail\fields;

use Craft;

use Cake\Utility\Hash;

class Checkboxes extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Checkboxes';
    public static $class = 'craft\fields\Checkboxes';


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
        // TODO: Implement parseField() method.
    }

}