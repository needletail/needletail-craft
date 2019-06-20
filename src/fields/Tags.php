<?php

namespace needletail\needletail\fields;

use Cake\Utility\Hash;
use Craft;
use craft\base\Element as BaseElement;
use craft\elements\db\TagQuery;
use craft\elements\Tag as TagElement;
use needletail\needletail\fields\Field;
use needletail\needletail\fields\FieldInterface;
use needletail\needletail\Needletail as Plugin;
use craft\helpers\Db;

class Tags extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Tags';
    public static $class = 'craft\fields\Tags';
    public static $elementType = 'craft\elements\Tag';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'needletail/_includes/fields/tags';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        /** @var TagQuery $value */
        $value =  $this->element->getFieldValue($this->fieldHandle);

        return array_values($value->select(['elements.id', 'title'])->pairs());
    }
}
