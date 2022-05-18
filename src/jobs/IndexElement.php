<?php

namespace needletail\needletail\jobs;

use Craft;
use craft\queue\BaseJob;
use needletail\needletail\models\BucketModel;
use needletail\needletail\Needletail;

class IndexElement extends BaseJob
{
    // Properties
    // =========================================================================

    /**
     * @var BucketModel
     */
    public $bucket;

    /**
     * @var $elementId
     */
    public $elementId;

    /**
     * @var $siteId
     */
    public $siteId;

    // Public Methods
    // =========================================================================

    public function execute($queue): void
    {
        $element = Craft::$app->elements->getElementById($this->elementId, null, $this->siteId);
        if ($element) {
            Needletail::$plugin->process->processSingle($this->bucket, $element);
        }
        $this->setProgress($queue, 100);
    }

    // Protected Methods
    // =========================================================================

    /**
     * Returns a default description for [[getDescription()]], if [[description]] isn’t set.
     *
     * @return string The default task description
     */
    protected function defaultDescription(): string
    {
        return Craft::t('needletail', 'Sending element to Needletail');
    }
}
