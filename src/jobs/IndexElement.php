<?php

namespace needletail\needletail\jobs;

use Craft;
use craft\base\ElementInterface;
use craft\queue\BaseJob;
use needletail\needletail\models\BucketModel;
use needletail\needletail\Needletail;
use yii\base\Exception;

class IndexElement extends BaseJob
{
    // Properties
    // =========================================================================

    /**
     * @var BucketModel
     */
    public $bucket;

    /**
     * @var ElementInterface
     */
    public $element;

    // Public Methods
    // =========================================================================

    public function execute($queue)
    {
        Needletail::$plugin->process->processSingle($this->bucket, $this->element);
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
        return Craft::t('needletail', 'Sending `{name}` to Needletail', ['name' => $this->element->title]);
    }
}
