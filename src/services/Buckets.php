<?php

namespace needletail\needletail\services;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\helpers\Json;
use needletail\needletail\events\BucketEvent;
use needletail\needletail\models\BucketModel;
use needletail\needletail\Needletail;
use needletail\needletail\records\BucketRecord;
use verbb\feedme\events\FeedEvent;
use verbb\feedme\records\FeedRecord;
use yii\base\Exception;
use yii\caching\DbQueryDependency;

class Buckets extends Component
{

    // Constants
    // =========================================================================

    const EVENT_BEFORE_SAVE_BUCKET = 'onBeforeSaveBucket';
    const EVENT_AFTER_SAVE_BUCKET = 'onAfterSaveBucket';
    const EVENT_BEFORE_DELETE_BUCKET = 'onBeforeDeleteBucket';
    const EVENT_AFTER_DELETE_BUCKET = 'onAfterDeleteBucket';

    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     Needletail::$plugin->buckets->getName()
     *
     * @return mixed
     */
    public function getName()
    {
        return Needletail::$plugin->name;
    }

    /**
     * @param array $criteria
     * @return BucketModel[]
     */
    public function getBuckets($criteria = [])
    {
        $query = $this->_getQuery()->where($criteria);

        $results = $query->all();

        foreach ($results as $key => $result) {
            $results[$key] = $this->_createModelFromRecord($result);
        }

        return $results;
    }

    /**
     * @return BucketModel[]
     */
    public function getCached()
    {
        $dep = new DbQueryDependency([
            'query'=> (new Query())
                ->from(BucketRecord::tableName())
                ->orderBy(['dateUpdated' => SORT_DESC ])
                ->limit(1)
                ->select('dateUpdated')
        ]);

        return Craft::$app->getCache()->getOrSet('needletail.buckets', function() {
            return Needletail::$plugin->buckets->getBuckets();
        }, null, $dep);
    }

    /**
     * @return BucketModel|null
     */
    public function getById($bucketId)
    {
        $result = $this->_getQuery()
            ->where(['id' => $bucketId])
            ->one();

        return $this->_createModelFromRecord($result);
    }

    public function getByHandle($handle, $includingPrefix = false)
    {
        if ( $includingPrefix )
            $handle = str_replace(Needletail::$plugin->settings->getBucketPrefix(), '', $handle);

        $result = $this->_getQuery()
            ->where(['handle' => $handle])
            ->one();

        return $this->_createModelFromRecord($result);
    }

    /**
     * @param BucketModel $model
     * @param bool $runValidation
     * @return bool
     */
    public function save(BucketModel $model, $runValidation = true)
    {
        $isNewModel = !$model->id;

        if (!$record = BucketRecord::findOne($model->id)) {
            $record = new BucketRecord();
        }

        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_BUCKET)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_BUCKET, new BucketEvent([
                'bucket' => $model,
                'isNew' => $isNewModel,
            ]));
        }

        $record->name = $model->name;
        $record->handle = $model->handle;
        $record->elementType = $model->elementType;
        $record->siteId = $model->siteId;

        if ($model->elementData) {
            $record->setAttribute('elementData', json_encode($model->elementData));
        }
        if ($model->fieldMapping) {
            $record->setAttribute('fieldMapping', json_encode($model->fieldMapping));
        }


        if ($runValidation && !$record->validate()) {
            Craft::info('Bucket not saved due to validation error.', __METHOD__);
            $model->addErrors($record->getErrors());
            return false;
        }

        $record->save(false);

        if (!$model->id) {
            $model->id = $record->id;
        }
        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_BUCKET)) {
            $this->trigger(self::EVENT_AFTER_SAVE_BUCKET, new BucketEvent([
                'bucket' => $model,
                'isNew' => $isNewModel,
            ]));
        }

        return true;
    }

    /**
     * @param $bucketId
     * @return bool
     * @throws \yii\db\Exception
     */
    public function deleteById($bucketId)
    {
        $model = $this->getById($bucketId);

        if ($model->id) {

            if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE_BUCKET)) {
                $this->trigger(self::EVENT_BEFORE_DELETE_BUCKET, new BucketEvent([
                    'bucket' => $model,
                ]));
            }

            Craft::$app->getDb()->createCommand()
                ->delete(BucketRecord::tableName(), ['id' => $model->id])
                ->execute();

            if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_BUCKET)) {
                $this->trigger(self::EVENT_AFTER_DELETE_BUCKET, new BucketEvent([
                    'bucket' => $model,
                ]));
            }
        }

        return true;
    }

    // Private Methods
    // =========================================================================

    private function _getQuery()
    {
        return BucketRecord::find()
            ->select([
                'id',
                'name',
                'handle',
                'elementType',
                'elementData',
                'fieldMapping',
                'siteId',
                'dateCreated',
                'dateUpdated',
                'uid',
            ]);
    }


    private function _createModelFromRecord(BucketRecord $record = null)
    {
        if (!$record) {
            return null;
        }

        $record['elementData'] = Json::decode($record['elementData']);
        $record['fieldMapping'] = Json::decode($record['fieldMapping']);

        $attributes = $record->toArray();

        return new BucketModel($attributes);
    }
}