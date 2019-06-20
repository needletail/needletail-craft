<?php
namespace needletail\needletail\fields;

use craft\elements\Category;
use craft\elements\Entry;

class Entries extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Entries';
    public static $class = 'craft\fields\Entries';
    public static $elementType = 'craft\elements\Entry';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'needletail/_includes/fields/entries';
    }

    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $query = $this->element->getFieldValue($this->fieldHandle);

        return array_map(function (Entry $entry) {
            return [
                'id' => $entry->id,
                'title' => $entry->title
            ];
        }, $query->all());
    }
}