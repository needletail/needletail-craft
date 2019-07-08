<?php

namespace needletail\needletail\fields;

use craft\base\Component;
use craft\base\ElementInterface;
use craft\elements\Asset;
use needletail\needletail\models\BucketModel;
use needletail\needletail\Needletail as Plugin;
use needletail\needletail\Needletail;

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

    /**
     * @var int
     */
    public $nestingLevel = 0;

    /**
     * When this is a nested field, we do not have settings on what to index.
     * When this is the case, we will use the default sub attributes specified here.
     * @var array
     */
    public $defaultSubAttributes = [];

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

    public function parseElementField()
    {
        $query = $this->element->getFieldValue($this->fieldHandle);
        $all = $query->all();

        $fieldMapping = $this->bucket->fieldMapping[$this->fieldHandle] ?? null;


        $fieldMapping = Plugin::$plugin->process->prepareMappingData($fieldMapping['fields'] ?? []);
        $element = Needletail::$plugin->elements->getRegisteredElement($this->elementType);

        return array_map(function (ElementInterface $el) use ($fieldMapping, $element) {
            $fieldData = [];
            $newNestingLevel = $this->nestingLevel + 1;


            if ( $this->nestingLevel < 1 ) {
                foreach (Needletail::$plugin->hash->get($fieldMapping, 'attributes', []) as $handle => $data) {
                    $fieldData[$handle] = $this->bucket->element->parseAttribute($el, $handle, $data);
                }

                foreach (Needletail::$plugin->hash->get($fieldMapping, 'fields', []) as $handle => $data) {
                    $fieldData[$handle] = Plugin::$plugin->fields->parseField($this->bucket, $el, $handle, $data, $newNestingLevel);
                }
            } else {
                foreach ( $this->defaultSubAttributes as $subAttribute ) {
                    $fieldData[$subAttribute] = $this->bucket->element->parseAttribute($el, $subAttribute, []);
                }
            }


            return array_merge([
                    'id' => (int) $el->id,
                ]) + $fieldData;
        }, $query->all());
    }
}