<?php
namespace needletail\needletail\fields;

use Craft;

class Redactor extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Redactor';
    public static $class = 'craft\redactor\Field';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'needletail/_includes/fields/_base';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $data = $this->element->getFieldValue($this->fieldHandle);
        if ( is_object($data) && get_class($data) === 'craft\redactor\FieldData') {
            /** @var craft\redactor\FieldData $data */
            return strip_tags($data->getParsedContent());
        }
        return $data;
    }
}
