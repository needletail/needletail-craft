<?php

namespace needletail\needletail\base;

use craft\base\ComponentInterface;
use craft\base\ElementInterface as CraftElementInterface;
use needletail\needletail\models\BucketModel;

interface ElementInterface extends ComponentInterface
{
    /**
     * @param BucketModel $bucket
     * @param array $params
     * @return \craft\elements\db\ElementQueryInterface
     */
    public function getQuery(BucketModel $bucket, $params = []);

    /**
     * @return \craft\elements\db\ElementQueryInterface[]
     */
    public function getQueries(BucketModel $bucket, array $params = []): array;

    public function getName();

    public function getElementClass();

    public function parseAttribute(CraftElementInterface $element, $handle, $data);

    public function includesElement(BucketModel $bucket, CraftElementInterface $element): bool;

    public function shouldIndexElement(BucketModel $bucket, CraftElementInterface $element): bool;
}