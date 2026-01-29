<?php

namespace needletail\needletail\jobs;

use Craft;
use craft\queue\BaseJob;
use needletail\needletail\models\BucketModel;
use needletail\needletail\Needletail;

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
        $element = $this->bucket->getElement();
        if (!$element) {
            return;
        }

        $stepSize = 100;

        $queries = $element->getQueries($this->bucket, []);

        // Remove null/empty values (and ensure numeric keys)
        $queries = array_values(array_filter($queries));

        $counts = [];
        $totalCount = 0;

        foreach ($queries as $index => $query) {
            $count = (int)(clone $query)->count();
            $counts[$index] = $count;
            $totalCount += $count;
        }

        if ($totalCount === 0) {
            return;
        }

        $processed = 0;

        foreach ($queries as $index => $query) {
            $count = $counts[$index] ?? 0;

            if ($count === 0) {
                continue;
            }

            $steps = (int)ceil($count / $stepSize);

            for ($i = 0; $i < $steps; $i++) {
                Needletail::$plugin->process->processBatchQuery($this->bucket, $query, $stepSize, $i * $stepSize);

                $processed += min($stepSize, $count - ($i * $stepSize));
                $this->setProgress($queue, min($processed / $totalCount, 1));
            }
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
