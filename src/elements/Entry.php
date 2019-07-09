<?php

namespace needletail\needletail\elements;

use Craft;
use craft\elements\Entry as EntryElement;
use craft\models\Section;
use needletail\needletail\base\Element;
use needletail\needletail\base\ElementInterface;
use needletail\needletail\models\BucketModel;

class Entry extends Element implements ElementInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Entry';
    public static $class = 'craft\elements\Entry';

    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'needletail/_includes/elements/entry/groups';
    }

    public function getMappingTemplate()
    {
        return 'needletail/_includes/elements/entry/map';
    }



    // Public Methods
    // =========================================================================

    public function getGroups()
    {
        // Get editable sections for user
        $editable = Craft::$app->sections->getEditableSections();

        // Get sections but not singles
        $sections = [];
        foreach ($editable as $section) {
            if ($section->type != Section::TYPE_SINGLE) {
                $sections[] = $section;
            }
        }

        return $sections;
    }

    /**
     * @param BucketModel $bucket
     * @param array $params
     * @return \craft\elements\db\ElementQueryInterface
     */
    public function getQuery(BucketModel $bucket, $params = [])
    {
        $query = EntryElement::find()
            ->status(EntryElement::STATUS_LIVE)
            ->sectionId($bucket->elementData[EntryElement::class]['section'])
            ->typeId($bucket->elementData[EntryElement::class]['entryType'])
            ->siteId($bucket->siteId ?: Craft::$app->getSites()->getPrimarySite()->id);

        Craft::configure($query, $params);

        return $query;
    }
}