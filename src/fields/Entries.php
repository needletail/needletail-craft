<?php

namespace needletail\needletail\fields;

use craft\elements\Category;
use craft\elements\Entry;
use needletail\needletail\Needletail as Plugin;
use needletail\needletail\Needletail;

class Entries extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Entries';
    public static $class = 'craft\fields\Entries';
    public static $elementType = 'craft\elements\Entry';

    public $defaultSubAttributes = [
        'title', 'slug', 'url'
    ];

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
        return $this->parseElementField();
    }
}