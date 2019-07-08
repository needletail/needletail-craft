<?php

namespace needletail\needletail\elements;

use Craft;
use craft\elements\Category as CategoryElement;
use needletail\needletail\base\Element;
use needletail\needletail\base\ElementInterface;
use needletail\needletail\models\BucketModel;

class Category extends Element implements ElementInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Category';
    public static $class = 'craft\elements\Category';

    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'needletail/_includes/elements/category/groups';
    }

    public function getMappingTemplate()
    {
        return 'needletail/_includes/elements/category/map';
    }


    // Public Methods
    // =========================================================================

    public function getGroups()
    {
        return Craft::$app->categories->getEditableGroups();
    }

    /**
     * @param BucketModel $bucket
     * @param array $params
     * @return \craft\elements\db\ElementQueryInterface
     */
    public function getQuery(BucketModel $bucket, $params = [])
    {
        $query = CategoryElement::find()
            ->anyStatus()
            ->groupId($bucket->elementData[CategoryElement::class])
            ->siteId($bucket->siteId ?: Craft::$app->getSites()->getPrimarySite()->id);

        Craft::configure($query, $params);

        return $query;
    }

}