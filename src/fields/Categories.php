<?php

namespace needletail\needletail\fields;

use craft\elements\Category;

class Categories extends Field implements FieldInterface
{
    public static $name = 'Categories';
    public static $class = 'craft\fields\Categories';
    public static $elementType = 'craft\elements\Category';

    public function getMappingTemplate()
    {
        return 'needletail/_includes/fields/categories';
    }

    public $defaultSubAttributes = [
        'title', 'slug', 'url'
    ];

    // Public Methods
    // =========================================================================

    public function parseField()
    {
        return $this->parseElementField();
    }
}