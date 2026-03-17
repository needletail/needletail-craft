<?php
/**
 * Needletail plugin for Craft CMS 3.x
 *
 * Needletail Search and Index package for Craft 3.x
 *
 * @link      https://needletail.io
 * @copyright Copyright (c) 2019 Needletail
 */

namespace needletail\needletail\console\controllers;

use Craft;
use needletail\needletail\jobs\IndexBucket;
use needletail\needletail\models\BucketModel;
use needletail\needletail\Needletail;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Default Command
 *
 * The first line of this class docblock is displayed as the description
 * of the Console Command in ./craft help
 *
 * Craft can be invoked via commandline console by using the `./craft` command
 * from the project root.
 *
 * Console Commands are just controllers that are invoked to handle console
 * actions. The segment routing is plugin-name/controller-name/action-name
 *
 * The actionIndex() method is what is executed if no sub-commands are supplied, e.g.:
 *
 * ./craft needletail/default
 *
 * Actions must be in 'kebab-case' so actionReindex() maps to 'reindex',
 * and would be invoked via:
 *
 * ./craft needletail/default/reindex
 *
 * @author    Needletail
 * @package   Needletail
 * @since     1.0.0
 */
class DefaultController extends Controller
{
    /**
     * Flush the bucket before queueing the full reindex.
     *
     * Exposed as `--flush-first`.
     *
     * @var bool
     */
    public $flushFirst = false;

    // Public Methods
    // =========================================================================

    /**
     * Queue a full reindex job for a specific bucket.
     *
     * The first line of this method docblock is displayed as the description
     * of the Console Command in ./craft help
     *
     * @param string|null $bucketIdentifier
     * @return int
     */
    public function actionIndex(?string $bucketIdentifier = null): int
    {
        if ($bucketIdentifier === null) {
            $this->stderr("A bucket ID or handle is required.\n", Console::FG_RED);
            $this->stdout("Usage: ./craft needletail/default/reindex <bucketIdOrHandle> [--flush-first=1]\n");

            return ExitCode::UNSPECIFIED_ERROR;
        }

        return $this->actionReindex($bucketIdentifier);
    }

    /**
     * Queue a full reindex job for a specific bucket.
     *
     * The first line of this method docblock is displayed as the description
     * of the Console Command in ./craft help
     *
     * @param string $bucketIdentifier
     * @return int
     */
    public function actionReindex(string $bucketIdentifier): int
    {
        $bucket = $this->resolveBucket($bucketIdentifier);

        if ($bucket === null) {
            $availableBuckets = array_map(function (BucketModel $bucketModel) {
                return sprintf('%d:%s', $bucketModel->id, $bucketModel->handleWithPrefix);
            }, Needletail::$plugin->buckets->getBuckets());

            $this->stderr(sprintf("Bucket `%s` was not found.\n", $bucketIdentifier), Console::FG_RED);

            if (!empty($availableBuckets)) {
                $this->stdout("Available buckets: " . implode(', ', $availableBuckets) . "\n");
            }

            return ExitCode::UNSPECIFIED_ERROR;
        }

        if ($this->flushFirst) {
            Needletail::$plugin->connection->truncateBucket($bucket->handleWithPrefix);

            $this->stdout(sprintf(
                "Flushed bucket `%s` before reindex.\n",
                $bucket->handleWithPrefix
            ), Console::FG_YELLOW);
        }

        Craft::$app->getQueue()->delay(0)->push(new IndexBucket([
            'bucket' => $bucket,
            'offset' => 0,
        ]));

        $this->stdout(sprintf(
            "Queued full reindex for bucket `%s`.\n",
            $bucket->handleWithPrefix
        ), Console::FG_GREEN);

        return ExitCode::OK;
    }

    public function options($actionId): array
    {
        $options = parent::options($actionId);

        if (in_array($actionId, ['index', 'reindex'], true)) {
            $options[] = 'flushFirst';
        }

        return $options;
    }

    public function optionAliases(): array
    {
        return array_merge(parent::optionAliases(), [
            'f' => 'flushFirst',
        ]);
    }

    private function resolveBucket(string $bucketIdentifier): ?BucketModel
    {
        if (is_numeric($bucketIdentifier)) {
            return Needletail::$plugin->buckets->getById((int)$bucketIdentifier);
        }

        return Needletail::$plugin->buckets->getByHandle($bucketIdentifier, true);
    }
}
