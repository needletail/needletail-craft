<?php
namespace needletail\needletail\fields;

use verbb\feedme\helpers\DateHelper;

use Craft;

use Cake\Utility\Hash;

class Date extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Date';
    public static $class = 'craft\fields\Date';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'needletail/_includes/fields/date';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        /** @var \DateTime $value */
        $value = $this->element->getFieldValue($this->fieldHandle);
        if ( ! $value )
            return null;
        if ( $this->field->showDate && ! $this->field->showTime ) {
            return $value->format('Y-m-d');
        } elseif ( ! $this->field->showDate && $this->field->showTime ) {
            return $value->format('H:i:s');
        } else {
            return $value->format('Y-m-d H:i:s');
        }
    }
}