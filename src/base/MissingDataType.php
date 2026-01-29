<?php

namespace needletail\needletail\base;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface as CraftElementInterface;
use craft\elements\Entry as EntryElement;
use needletail\needletail\fields\FieldInterface as NeedletailFieldInterface;
use needletail\needletail\models\BucketModel;

/**
 * Fallback component used when a registered Element/Field type cannot be created.
 *
 * This prevents hard failures if a third-party plugin registers an incompatible type.
 */
class MissingDataType extends Component implements ElementInterface, NeedletailFieldInterface
{
    public static $name = 'Missing data type';

    /**
     * Set to an empty string so Elements/Fields services will skip registering it.
     *
     * @var string
     */
    public static $class = '';

    /**
     * @var string|null
     */
    public $errorMessage;

    /**
     * @var string|null
     */
    public $expectedType;

    public function getQuery(BucketModel $bucket, $params = [])
    {
        // Return a safe, empty query.
        $query = EntryElement::find()->id(false);
        Craft::configure($query, $params);
        return $query;
    }

    public function getQueries(BucketModel $bucket, array $params = []): array
    {
        return [$this->getQuery($bucket, $params)];
    }

    public function getName()
    {
        return self::$name;
    }

    public function getElementClass()
    {
        return self::$class;
    }

    public function getFieldClass()
    {
        return self::$class;
    }

    public function getElementType()
    {
        return '';
    }

    public function parseAttribute(CraftElementInterface $element, $handle, $data)
    {
        return null;
    }

    public function includesElement(BucketModel $bucket, CraftElementInterface $element): bool
    {
        return false;
    }

    public function shouldIndexElement(BucketModel $bucket, CraftElementInterface $element): bool
    {
        return false;
    }

    public function getMappingTemplate()
    {
        // Use the default mapping row template as a safe fallback.
        return 'needletail/_includes/fields/default';
    }

    public function parseField()
    {
        return null;
    }
}

