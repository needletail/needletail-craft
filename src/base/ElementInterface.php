<?php

namespace needletail\needletail\base;

use craft\base\ComponentInterface;
use needletail\needletail\models\BucketModel;

interface ElementInterface extends ComponentInterface
{
    /**
     * @param BucketModel $bucket
     * @param array $params
     * @return \craft\elements\db\ElementQueryInterface
     */
    public function getQuery(BucketModel $bucket, $params = []);

}