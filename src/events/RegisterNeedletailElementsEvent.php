<?php

namespace needletail\needletail\events;

use yii\base\Event;

class RegisterNeedletailElementsEvent extends Event
{
    // Properties
    // =========================================================================

    public $elements = [];
}