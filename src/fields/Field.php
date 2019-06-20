<?php

namespace needletail\needletail\fields;

use craft\base\Component;
use craft\base\ElementInterface;
use needletail\needletail\models\BucketModel;

abstract class Field extends Component
{
    public $data;

    /**
     * @var \craft\base\FieldInterface
     */
    public $field;

    /**
     * @var string
     */
    public $fieldHandle;

    /**
     * @var ElementInterface
     */
    public $element;

    /**
     * @var BucketModel
     */
    public $bucket;

    // Public Methods
    // =========================================================================

    public function getName()
    {
        return $this::$name;
    }

    public function getClass()
    {
        return get_class($this);
    }

    public function getFieldClass()
    {
        return $this::$class;
    }

    public function getElementType()
    {
        return $this::$elementType;
    }
}