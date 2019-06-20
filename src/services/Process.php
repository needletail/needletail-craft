<?php

namespace needletail\needletail\services;

use Cake\Utility\Hash;
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
        $query = $bucket->element->getQuery($bucket, []);
        $query->offset($offset);
        $query->limit($take);
        $results = $query->all();

        $mappingData = $this->prepareMappingData($bucket->fieldMapping);

        $results = array_map(function (ElementInterface $element) use ($bucket, $mappingData) {
            return $this->parseElement($element, $bucket, $mappingData);
        }, $results);

        Needletail::$plugin->connection->bulk($bucket->handle, $results);
    }

    public function afterProcess()
    {

    }

    public function prepareMappingData(array $data)
    {
        $mappingData = [
            'attributes' => [],
            'fields' => []
        ];

        foreach ( $data as $handle => $settings ) {
            if ( ! Hash::get($settings, 'enabled') ) {
                continue;
            }
            unset($settings['enabled']);
            $target = array_key_exists('field', $settings) ? 'fields' : 'attributes';
            if ( array_key_exists('fields', $settings) ) {
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

        foreach ( Hash::get($mappingData, 'attributes', []) as $handle => $data) {
            $fieldData[$handle] = $bucket->element->parseAttribute($element, $handle, $data);
        }
        foreach ( Hash::get($mappingData, 'fields', []) as $handle => $data) {
            $fieldData[$handle] = Plugin::$plugin->fields->parseField($bucket, $element, $handle, $data);
        }

        return array_merge([
            'id' => (int) $element->id,
        ]) + $fieldData;

    }
}