<?php

namespace needletail\needletail\services;

use craft\base\Component;
use craft\base\ElementInterface;
use craft\elements\Asset as AssetElement;
use craft\elements\Category as CategoryElement;
use craft\elements\Entry as EntryElement;
use craft\helpers\Json;
use needletail\needletail\base\ParsesSelf;
use needletail\needletail\Needletail as Plugin;
use craft\helpers\App;
use needletail\needletail\models\BucketModel;
use needletail\needletail\Needletail;
use craft\elements\db\ElementQueryInterface;

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

        $this->processBatchQuery($bucket, $query, $take, $offset);
    }

    public function processBatchQuery(BucketModel $bucket, ElementQueryInterface $query, int $take, int $offset): void
    {
        if ($this->shouldNotPerformWriteActions()) {
            return;
        }

        $batchQuery = clone $query;
        $batchQuery->offset($offset);
        $batchQuery->limit($take);
        $results = $batchQuery->all();

        if ($bucket->customMappingFile) {
            $results = array_map(function (ElementInterface $element) use ($bucket) {
                if (file_exists(\Craft::$app->path->getSiteTemplatesPath().'/_needletail/'.$bucket->mappingTwigFile)) {
                    $rendered = \Craft::$app->getView()->renderString(file_get_contents(\Craft::$app->path->getSiteTemplatesPath().'/_needletail/'.$bucket->mappingTwigFile), [
                        'entry' => $element
                    ]);

                    $rendered = $this->replaceNewlineInQuotes($rendered);
                    $array = Json::decodeIfJson($rendered);

                    if (is_null($array)) {
                        throw new \Exception('Custom mapping file is not valid JSON: '.$rendered);
                    }

                    return array_merge([
                        'id' => (int)$element->id,
                    ]) + $array;
                }

                throw new \Exception('Custom mapping file not found');
            }, $results);
        } else {
            $mappingData = $this->prepareMappingData($bucket->fieldMapping);

            $results = array_map(function (ElementInterface $element) use ($bucket, $mappingData) {
                return $this->parseElement($element, $bucket, $mappingData);
            }, $results);
        }

        // Only keep $results where there is data other than just 'id' (filter when only id is available)
        $results = array_filter($results, function ($result) {
            if (!is_array($result)) {
                return false;
            }
            // If the only key present is 'id', filter it out
            $keys = array_keys($result);
            return count($keys) > 1 || (count($keys) == 1 && $keys[0] !== 'id');
        });

        if (empty($results)) {
            return;
        }

        Needletail::$plugin->connection->bulk($bucket->handleWithPrefix, $results);
    }

    public function processSingle(BucketModel $bucket, ElementInterface $element)
    {
        if ( $this->shouldNotPerformWriteActions() ) {
            return false;
        }

        if ($bucket->customMappingFile) {
            if (file_exists(\Craft::$app->path->getSiteTemplatesPath().'/_needletail/'.$bucket->mappingTwigFile)) {
                $rendered = \Craft::$app->getView()->renderString(file_get_contents(\Craft::$app->path->getSiteTemplatesPath().'/_needletail/'.$bucket->mappingTwigFile), [
                    'entry' => $element
                ]);

                $rendered = $this->replaceNewlineInQuotes($rendered);
                $array = Json::decodeIfJson($rendered);

                if (is_null($array)) {
                    throw new \Exception('Custom mapping file is not valid JSON: '.$rendered);
                }

                $result =  array_merge([
                        'id' => (int)$element->id,
                    ]) + $array;
            } else {
                throw new \Exception('Custom mapping file not found');
            }
        } else {
            $mappingData = $this->prepareMappingData($bucket->fieldMapping);

            $result = $this->parseElement($element, $bucket, $mappingData);
        }

        // If result only contains 'id', return early
        if (is_array($result) && count($result) === 1 && array_key_exists('id', $result)) {
            return;
        }

        Needletail::$plugin->connection->update($bucket->handleWithPrefix, $result);
    }

    public function deleteSingle(BucketModel $bucket, ?ElementInterface $element = null, ?int $elementId = null)
    {
        if ( $this->shouldNotPerformWriteActions() )
            return false;

        Needletail::$plugin->connection->delete($bucket->handleWithPrefix, $elementId ?? $element->getId());
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
        if ($bucket->element instanceof ParsesSelf)
            return $bucket->element->parseElement($element, $bucket, $mappingData);

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
        return App::env('CRAFT_ENVIRONMENT') !== 'production';
    }

    public function nonProductionIsDisabled()
    {
        return !! Needletail::$plugin->settings->disableIndexingOnNonProduction;
    }

    function replaceNewlineInQuotes($json) {
        return preg_replace_callback('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"/s', function($matches) {
            return '"' . str_replace("\n", "\\n", $matches[1]) . '"';
        }, $json);
    }
}
