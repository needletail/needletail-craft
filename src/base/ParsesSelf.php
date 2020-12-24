<?php

namespace needletail\needletail\base;

use craft\base\ElementInterface;
use needletail\needletail\models\BucketModel;

interface ParsesSelf
{
    public function parseElement(ElementInterface $element, BucketModel $bucket, $mappingData);
}
