<?php

namespace needletail\needletail\fields;

use Cake\Utility\Hash;

class Table extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Table';
    public static $class = 'craft\fields\Table';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'needletail/_includes/fields/table';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $fields = [];

        $map = Hash::get($this->bucket->fieldMapping, $this->fieldHandle . '.fields');

        foreach ( $map as $col ) {
            if ( Hash::get($col, 'enabled') == "1") {
                $fields[] = Hash::get($col, 'handle');
            }
        }

        return array_map( function ($data) use ($fields) {
            return array_intersect_key($data, array_flip((array) $fields));
        }, (array) $this->element->getFieldValue($this->fieldHandle));
    }
}
