<?php
namespace needletail\needletail\events;

use yii\base\Event;

class BucketEvent extends Event
{
    // Properties
    // =========================================================================

    public $bucket;
    public $isNew = false;
}
