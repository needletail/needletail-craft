<?php

namespace needletail\needletail\jobs;

use Craft;
use craft\base\ElementInterface;
use craft\queue\BaseJob;
use needletail\needletail\models\BucketModel;
use needletail\needletail\Needletail;
use yii\base\Exception;

class DeleteElement extends BaseJob
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

    // Public Methods
    // =========================================================================

    public function execute($queue)
    {
        Needletail::$plugin->process->deleteSingle($this->bucket, null, $this->elementId);
        $this->setProgress($queue, 100);

        return true;
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
