<?php

namespace needletail\needletail\services;

use craft\base\Component;
use craft\base\ElementInterface;
use needletail\needletail\Needletail as Plugin;
use craft\helpers\App;
use needletail\needletail\models\BucketModel;
use needletail\needletail\Needletail;

class Process extends Component
{
    public function beforeProcess()
    {
        App::maxPowerCaptain();
    }

    public function processBatch(BucketModel $bucket, int $take, int $offset)
    {
        if ($this->shouldNotPerformWriteActions())
            return false;

        $query = $bucket->element->getQuery($bucket, []);
        $query->offset($offset);
        $query->limit($take);
        $results = $query->all();

        $mappingData = $this->prepareMappingData($bucket->fieldMapping);

        $results = array_map(function (ElementInterface $element) use ($bucket, $mappingData) {
            return $this->parseElement($element, $bucket, $mappingData);
        }, $results);

        Needletail::$plugin->connection->bulk($bucket->handleWithPrefix, $results);
    }

    public function processSingle(BucketModel $bucket, ElementInterface $element)
    {
        if ( $this->shouldNotPerformWriteActions() )
            return false;

        $mappingData = $this->prepareMappingData($bucket->fieldMapping);

        $result = $this->parseElement($element, $bucket, $mappingData);

        Needletail::$plugin->connection->update($bucket->handleWithPrefix, $result);
    }

    public function deleteSingle(BucketModel $bucket, ElementInterface $element)
    {
        if ( $this->shouldNotPerformWriteActions() )
            return false;

        Needletail::$plugin->connection->delete($bucket->handleWithPrefix, $element->getId());
    }

    public function afterProcess()
    {

    }

    public function prepareMappingData($data)
    {
        $mappingData = [
            'attributes' => [],
            'fields' => []
        ];


        foreach ($data as $handle => $settings) {
            if (!Needletail::$plugin->hash->get($settings, 'enabled')) {
                continue;
            }
            unset($settings['enabled']);
            $target = array_key_exists('field', $settings) ? 'fields' : 'attributes';
            if (array_key_exists('fields', $settings)) {
                $settings['children'] = $this->prepareMappingData($settings['fields']);
                unset($settings['fields']);
            }
            $mappingData[$target][$handle] = $settings;
        }

        return $mappingData;
    }

    public function parseElement(ElementInterface $element, BucketModel $bucket, $mappingData)
    {
        $fieldData = [];

        foreach (Needletail::$plugin->hash->get($mappingData, 'attributes', []) as $handle => $data) {
            $fieldData[$handle] = $bucket->element->parseAttribute($element, $handle, $data);
        }
        foreach (Needletail::$plugin->hash->get($mappingData, 'fields', []) as $handle => $data) {
            $fieldData[$handle] = Plugin::$plugin->fields->parseField($bucket, $element, $handle, $data);
        }

        return array_merge([
                'id' => (int)$element->id,
            ]) + $fieldData;

    }

    public function shouldNotPerformWriteActions()
    {
        return $this->isRunningInNonProduction() && $this->nonProductionIsDisabled();
    }

    public function isRunningInNonProduction()
    {
        return CRAFT_ENVIRONMENT !== 'production';
    }

    public function nonProductionIsDisabled()
    {
        return !! Needletail::$plugin->settings->disableIndexingOnNonProduction;
    }
}