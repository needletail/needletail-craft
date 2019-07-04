<?php

namespace needletail\needletail\fields;

use Cake\Utility\Hash;
use craft\elements\Asset;
use craft\elements\Entry;
use needletail\needletail\Needletail as Plugin;
use needletail\needletail\Needletail;

class Assets extends Field implements FieldInterface
{
    public static $name = 'Assets';
    public static $class = 'craft\fields\Assets';
    public static $elementType = 'craft\elements\Asset';

    public $defaultSubAttributes = [
        'title', 'filename', 'size', 'url'
    ];

    public function getMappingTemplate()
    {
        return 'needletail/_includes/fields/assets';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        return $this->parseElementField();
    }
}