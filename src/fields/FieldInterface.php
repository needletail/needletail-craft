<?php

namespace needletail\needletail\fields;

use craft\base\ComponentInterface;

interface FieldInterface extends ComponentInterface
{
    // Public Methods
    // =========================================================================

    public function getMappingTemplate();

    public function parseField();
}