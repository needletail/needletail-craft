<?php

namespace needletail\needletail\services;

use Craft;
use craft\base\Component;
use craft\helpers\Json;
use needletail\needletail\events\BucketEvent;
use needletail\needletail\models\BucketModel;
use needletail\needletail\Needletail;
use needletail\needletail\records\BucketRecord;
use verbb\feedme\events\FeedEvent;
use verbb\feedme\records\FeedRecord;
use yii\base\Exception;

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


    public function getBuckets($orderBy = null)
    {
        $query = $this->_getQuery();

        if ($orderBy) {
            $query->orderBy($orderBy);
        }

        $results = $query->all();

        foreach ($results as $key => $result) {
            $results[$key] = $this->_createModelFromRecord($result);
        }

        return $results;
    }

    public function getById($bucketId)
    {
        $result = $this->_getQuery()
            ->where(['id' => $bucketId])
            ->one();

        return $this->_createModelFromRecord($result);
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

    public function save(BucketModel $model, bool $runValidation = true): bool
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
}