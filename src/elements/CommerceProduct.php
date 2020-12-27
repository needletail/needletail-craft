<?php

namespace needletail\needletail\elements;

use Cake\Utility\Hash;
use Craft;
use craft\commerce\elements\Product;
use craft\commerce\elements\Product as ProductElement;
use craft\commerce\elements\Variant as VariantElement;
use craft\commerce\Plugin as Commerce;
use craft\feedme\helpers\DataHelper;
use craft\feedme\Plugin;
use craft\helpers\StringHelper;
use needletail\needletail\base\Element;
use needletail\needletail\base\ElementInterface;
use needletail\needletail\base\ParsesSelf;
use needletail\needletail\models\BucketModel;
use needletail\needletail\Needletail;

/**
 *
 * @property-read string $mappingTemplate
 * @property-read mixed $groups
 * @property-write mixed $model
 * @property-read string $groupsTemplate
 * @property-read string $columnTemplate
 */
class CommerceProduct extends Element implements ElementInterface, ParsesSelf
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static $name = 'Commerce Product';

    /**
     * @var string
     */
    public static $class = 'craft\commerce\elements\Product';

    /**
     * @var
     */
    public $element;


    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getGroupsTemplate()
    {
        return 'needletail/_includes/elements/commerce-products/groups';
    }

    /**
     * @inheritDoc
     */
    public function getMappingTemplate()
    {
        return 'needletail/_includes/elements/commerce-products/map';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getGroups()
    {
        if (Commerce::getInstance()) {
            return Commerce::getInstance()->getProductTypes()->getEditableProductTypes();
        }
    }

    /**
     * @inheritDoc
     */
    public function getQuery(BucketModel $bucket, $params = [])
    {
        $query = ProductElement::find()
            ->anyStatus()
            ->typeId($bucket->getElementData()[ProductElement::class])
            ->with('variants')
            ->siteId($bucket->siteId ?: Craft::$app->getSites()->getPrimarySite()->id);
        Craft::configure($query, $params);
        return $query;
    }


    public function parseElement(\craft\base\ElementInterface $element, BucketModel $bucket, $mappingData)
    {
        /** @var Product $element */

        $fieldData = [];

        $attributes = Needletail::$plugin->hash->get($mappingData, 'attributes', []);
        $fields = Needletail::$plugin->hash->get($mappingData, 'fields', []);

        $productAttributes = array_filter($attributes, function ($key) {
            return !StringHelper::startsWith($key, 'variant-');
        }, ARRAY_FILTER_USE_KEY);

        $productFields = array_filter($fields, function ($key) {
            return !StringHelper::startsWith($key, 'variant-');
        }, ARRAY_FILTER_USE_KEY);

        $variantAttributes = array_filter($attributes, function ($key) {
            return StringHelper::startsWith($key, 'variant-');
        }, ARRAY_FILTER_USE_KEY);

        $variantFields = array_filter($fields, function ($key) {
            return StringHelper::startsWith($key, 'variant-');
        }, ARRAY_FILTER_USE_KEY);

        foreach ($productAttributes as $handle => $data) {
            $fieldData[$handle] = $bucket->element->parseAttribute($element, $handle, $data);
        }
        foreach ($productFields as $handle => $data) {
            $fieldData[$handle] = \needletail\needletail\Needletail::$plugin->fields->parseField($bucket, $element, $handle, $data);
        }

        $variants = [];
        if ($element->variants && is_array($element->variants) && count($element->variants)) {
            foreach ($element->variants as $variant) {
                $append = [];

                foreach ($variantAttributes as $handle => $data) {
                    $handle = StringHelper::afterFirst($handle, 'variant-');
                    $append[$handle] = $bucket->element->parseAttribute($variant, $handle, $data);
                }
                foreach ($variantFields as $handle => $data) {
                    $handle = StringHelper::afterFirst($handle, 'variant-');
                    $append[$handle] = \needletail\needletail\Needletail::$plugin->fields->parseField($bucket, $variant, $handle, $data);
                }

                $variants[] = $append;
            }
        }

        return array_merge([
            'id' => (int)$element->id,
        ], $fieldData, ['variants' => $variants]);
    }


    public function parseType($element, $data)
    {
        return $element->type->name;
    }

    public function parsePrice($element, $data)
    {
        return number_format($element->price, 2, '.', '');
    }
}
