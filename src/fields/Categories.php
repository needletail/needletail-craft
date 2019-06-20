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


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $query = $this->element->getFieldValue($this->fieldHandle);

        return array_map(function (Category $category) {
            return [
                'id' => $category->id,
                'title' => $category->title
            ];
        }, $query->all());
    }
}