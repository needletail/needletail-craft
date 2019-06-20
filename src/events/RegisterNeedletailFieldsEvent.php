<?php

namespace needletail\needletail\events;

use yii\base\Event;

class RegisterNeedletailFieldsEvent extends Event
{
    // Properties
    // =========================================================================

    public $fields = [];
}