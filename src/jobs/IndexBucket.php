<?php

namespace needletail\needletail\jobs;

use Craft;
use craft\queue\BaseJob;
use needletail\needletail\models\BucketModel;
use needletail\needletail\Needletail;
use yii\base\Exception;

class IndexBucket extends BaseJob
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
    public $offset;


    // Public Methods
    // =========================================================================

    public function execute($queue): void
    {
        $query = $this->bucket->getElement()->getQuery($this->bucket, []);
        $elementCount = $query->count();
        $stepSize = 250;

        if ( $elementCount == 0 )
            return;

        $steps = ceil($elementCount / $stepSize);

        for($i=0;$i<$steps;$i++) {
            Needletail::$plugin->process->processBatch($this->bucket, $stepSize, $i * $stepSize);
            $this->setProgress($queue, ($i+1) / $steps);
        }
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
        return Craft::t('needletail', 'Sending `{name}` to Needletail', ['name' => $this->bucket->name]);
    }
}
