<?php

namespace needletail\needletail\elements;

use Craft;
use craft\elements\Asset as AssetElement;
use craft\elements\Category as CategoryElement;
use needletail\needletail\base\Element;
use needletail\needletail\base\ElementInterface;
use needletail\needletail\models\BucketModel;

class Asset extends Element implements ElementInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Asset';
    public static $class = 'craft\elements\Asset';

    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'needletail/_includes/elements/asset/groups';
    }

    public function getMappingTemplate()
    {
        return 'needletail/_includes/elements/asset/map';
    }


    // Public Methods
    // =========================================================================

    public function getGroups()
    {
        return Craft::$app->volumes->getAllVolumes();
    }

    /**
     * @param BucketModel $bucket
     * @param array $params
     * @return \craft\elements\db\ElementQueryInterface
     */

    public function getQuery(BucketModel $bucket, $params = [])
    {
        $query = AssetElement::find()
            ->anyStatus()
            ->volumeId($bucket->getElementData()[AssetElement::class])
            ->includeSubfolders()
            ->siteId($bucket->siteId ?: Craft::$app->getSites()->getPrimarySite()->id);

        Craft::configure($query, $params);

        return $query;
    }

    public function parseSize($element, $data)
    {
        return $this->parseAnInteger($element->size);
    }

}
