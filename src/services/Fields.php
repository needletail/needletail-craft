<?php
namespace needletail\needletail\services;

use craft\base\ElementInterface;
use needletail\needletail\events\RegisterNeedletailFieldsEvent;
use needletail\needletail\fields\Assets;
use needletail\needletail\fields\Categories;
use needletail\needletail\fields\Checkboxes;
use needletail\needletail\fields\Date;
use needletail\needletail\fields\DefaultField;
use needletail\needletail\fields\Dropdown;
use needletail\needletail\fields\Entries;
use needletail\needletail\fields\FieldInterface;
use needletail\needletail\fields\Lightswitch;
use needletail\needletail\fields\Matrix;
use needletail\needletail\fields\Number;
use needletail\needletail\fields\RadioButtons;
use needletail\needletail\fields\Table;
use needletail\needletail\fields\Tags;
use needletail\needletail\fields\Users;
use needletail\needletail\models\BucketModel;
use verbb\feedme\FeedMe;
use verbb\feedme\events\RegisterFeedMeFieldsEvent;
use verbb\feedme\events\FieldEvent;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\helpers\Component as ComponentHelper;

use Cake\Utility\Hash;

class Fields extends Component
{
    // Constants
    // =========================================================================

    const EVENT_REGISTER_NEEDLETAIL_FIELDS = 'registerNeedletailFields';


    // Properties
    // =========================================================================

    private $_fields = [];
    private $_fieldsByHandle = [];


    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        // Load all fieldtypes once, used for later
        // foreach (Craft::$app->fields->getAllFields() as $field) {
        //     $this->_fieldsByHandle[$field->handle][] = $field;
        // }

        foreach ($this->getRegisteredFields() as $fieldClass) {
            $field = $this->createField($fieldClass);

            // Does this field exist in Craft right now?
            if (!class_exists($field::$class)) {
                continue;
            }

            $handle = $field::$class;

            $this->_fields[$handle] = $field;
        }
    }

    public function getRegisteredField($handle)
    {
        if (isset($this->_fields[$handle])) {
            return $this->_fields[$handle];
        } else {
            return $this->createField(DefaultField::class);
        }
    }

    public function fieldsList()
    {
        $list = [];

        foreach ($this->_fields as $handle => $field) {
            $list[$handle] = $field::$name;
        }

        return $list;
    }

    public function getRegisteredFields()
    {
        if (count($this->_fields)) {
            return $this->_fields;
        }
        
        $event = new RegisterNeedletailFieldsEvent([
            'fields' => [
                Assets::class,
                Categories::class,
                Checkboxes::class,
                Dropdown::class,
                Date::class,
                Entries::class,
                Lightswitch::class,
                Matrix::class,
                Number::class,
                RadioButtons::class,
                Table::class,
                Tags::class,
                Users::class,
            ],
        ]);

        $this->trigger(self::EVENT_REGISTER_NEEDLETAIL_FIELDS, $event);

        return $event->fields;
    }

    public function createField($config)
    {
        if (is_string($config)) {
            $config = ['type' => $config];
        }

        try {
            $field = ComponentHelper::createComponent($config, FieldInterface::class);
        } catch (MissingComponentException $e) {
            $config['errorMessage'] = $e->getMessage();
            $config['expectedType'] = $config['type'];
            unset($config['type']);

            $field = new MissingDataType($config);
        }

        return $field;
    }

    public function parseField(BucketModel $bucket, ElementInterface $element, $fieldHandle, $fieldInfo)
    {
//        if ($this->hasEventHandlers(self::EVENT_BEFORE_PARSE_FIELD)) {
//            $this->trigger(self::EVENT_BEFORE_PARSE_FIELD, new FieldEvent([
//                'fieldHandle' => $fieldHandle,
//                'element' => $element,
//                'bucket' => $bucket,
//            ]));
//        }

        $parsedValue = null;

        $fieldClassHandle = Hash::get($fieldInfo, 'field');

        // Find the class to deal with the attribute
        $class = $this->getRegisteredField($fieldClassHandle);
        $class->fieldHandle = $fieldHandle;
        $class->field = Craft::$app->fields->getFieldByHandle($fieldHandle);
        $class->element = $element;
        $class->bucket = $bucket;

        // Get that sweet data
        $parsedValue = $class->parseField();

        // We don't really want to set an empty array on fields, which is dangerous for existing date (elements)
        // But empty strings and booleans are totally fine, and desirable.
        // if (is_array($parsedValue) && empty($parsedValue)) {
        //     $parsedValue = null;
        // }

//        if ($this->hasEventHandlers(self::EVENT_AFTER_PARSE_FIELD)) {
//            $this->trigger(self::EVENT_AFTER_PARSE_FIELD, new FieldEvent([
//                'fieldHandle' => $fieldHandle,
//                'element' => $element,
//                'bucket' => $bucket,
//                'parsedValue' => $parsedValue,
//            ]));
//        }

        return $parsedValue;
    }

}
