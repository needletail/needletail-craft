<?php

namespace needletail\needletail\models;

use craft\base\Model;
use craft\helpers\Json as JsonHelper;
use needletail\needletail\base\ElementInterface;
use needletail\needletail\Needletail;

/**
 * Class BucketModel
 * @package needletail\needletail\models
 * @property ElementInterface $element
 * @property string $handleWithPrefix
 */
class BucketModel extends Model
{
    // Properties
    // =========================================================================

    public $id;
    public $name;
    public $handle;
    public $elementType;
    public $elementData;
    public $fieldMapping;
    public $siteId;
    public $dateCreated;
    public $dateUpdated;
    public $uid;

    // Public Methods
    // =========================================================================

    public function __toString()
    {
        return Craft::t('needletail', $this->name);
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['name', 'handle'], 'required'],
            [['handle'], 'unique']
        ];
    }

    /**
     * @return ElementInterface
     */
    public function getElement()
    {

        $element = Needletail::$plugin->elements->getRegisteredElement($this->elementType);

        if ($element) {
            $element->bucket = $this;
        }

        return $element;
    }

    public function getHandleWithPrefix()
    {
        return sprintf('%s%s', Needletail::$plugin->settings->getBucketPrefix(), $this->handle);
    }

    public function getElementData()
    {
        if (!is_array($this->elementData)) {
            return JsonHelper::decode($this->elementData);
        }
        return $this->elementData;
    }
}
