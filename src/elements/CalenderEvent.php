<?php

namespace needletail\needletail\elements;

use Craft;
use craft\elements\Asset as AssetElement;
use needletail\needletail\base\ParsesSelf;
use Solspace\Calendar\Elements\Event as SolspaceEvent;
use Solspace\Calendar\Calendar as SolspacePlugin;
use needletail\needletail\base\Element;
use needletail\needletail\base\ElementInterface;
use needletail\needletail\models\BucketModel;

class CalenderEvent extends Element implements ElementInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Calender Events';
    public static $class = 'Solspace\Calendar\Elements\Event';

    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'needletail/_includes/elements/calender-events/groups';
    }

    public function getMappingTemplate()
    {
        return 'needletail/_includes/elements/calender-events/map';
    }


    // Public Methods
    // =========================================================================

    public function getGroups()
    {
        if (SolspacePlugin::getInstance()) {
            return SolspacePlugin::getInstance()->calendars->getAllCalendars();
        }
    }

    /**
     * @param BucketModel $bucket
     * @param array $params
     * @return \craft\elements\db\ElementQueryInterface
     */

    public function getQuery(BucketModel $bucket, $params = [])
    {
        $query = SolspaceEvent::find()
            ->anyStatus()
            ->setCalendarId($bucket->getElementData()[SolspaceEvent::class])
            ->siteId($bucket->siteId ?: Craft::$app->getSites()->getPrimarySite()->id);

        Craft::configure($query, $params);

        return $query;
    }
}
