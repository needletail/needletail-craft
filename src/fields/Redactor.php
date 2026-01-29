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
        if (is_object($data) && method_exists($data, 'getParsedContent')) {
            return strip_tags((string)$data->getParsedContent());
        }
        return $data;
    }
}
