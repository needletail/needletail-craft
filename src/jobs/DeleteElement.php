<?php

namespace needletail\needletail\jobs;

use Craft;
use craft\queue\BaseJob;
use needletail\needletail\models\BucketModel;
use needletail\needletail\Needletail;

class DeleteElement extends BaseJob
{
    // Properties
    // =========================================================================

    /**
     * @var BucketModel
     */
    public $bucket;

    /**
     * @var int
     */
    public $elementId;

    // Public Methods
    // =========================================================================

    public function execute($queue): void
    {
        Needletail::$plugin->process->deleteSingle($this->bucket, null, $this->elementId);
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
        return Craft::t('needletail', 'Deleting element from Needletail');
    }
}
