<?php

namespace needletail\needletail\fields;

use craft\elements\Asset;

class Assets extends Field implements FieldInterface
{
    public static $name = 'Assets';
    public static $class = 'craft\fields\Assets';
    public static $elementType = 'craft\elements\Asset';

    public function getMappingTemplate()
    {
        return 'needletail/_includes/fields/assets';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $query = $this->element->getFieldValue($this->fieldHandle);

        return array_map(function (Asset $asset) {
            return [
                'id' => $asset->id,
                'filename' => $asset->filename,
                'url' => $asset->getUrl()
            ];
        }, $query->all());
    }
}