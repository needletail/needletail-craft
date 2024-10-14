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

        if ($bucket->customMappingFile) {
            $mapped = [];

            foreach ($results as $element) {
                if (file_exists(\Craft::$app->path->getSiteTemplatesPath().'/_needletail/'.$bucket->mappingTwigFile)) {
                    $rendered = \Craft::$app->getView()->renderString(file_get_contents(\Craft::$app->path->getSiteTemplatesPath().'/_needletail/'.$bucket->mappingTwigFile), [
                        'entry' => $element
                    ]);
                    $rendered = $this->replaceNewlineInQuotes($rendered);
                    $array = Json::decodeIfJson($rendered);

                    if (!is_array($array)) {
                        throw new \Exception('Custom mapping file is not valid JSON: '.$rendered);
                    }

                    if (array_keys($array) !== range(0, count($array) - 1)) { // Is assoc array
                        $mapped[] = array_merge([
                            'id' => (int)$element->id,
                        ], $array);
                    } else {
                        foreach ($array as $item) {
                            $mapped[] = $item;
                        }
                    }
                } else {
                    throw new \Exception('Custom mapping file not found');
                }
            }

            $results = $mapped;
        } else {
            $mappingData = $this->prepareMappingData($bucket->fieldMapping);

            $results = array_map(function (ElementInterface $element) use ($bucket, $mappingData) {
                return $this->parseElement($element, $bucket, $mappingData);
            }, $results);
        }

        Needletail::$plugin->connection->bulk($bucket->handleWithPrefix, $results);
    }

    public function processSingle(BucketModel $bucket, ElementInterface $element)
    {
        if ( $this->shouldNotPerformWriteActions() )
            return false;

        $isAssoc = true;
        if ($bucket->customMappingFile) {
            if (file_exists(\Craft::$app->path->getSiteTemplatesPath().'/_needletail/'.$bucket->mappingTwigFile)) {
                $rendered = \Craft::$app->getView()->renderString(file_get_contents(\Craft::$app->path->getSiteTemplatesPath().'/_needletail/'.$bucket->mappingTwigFile), [
                    'entry' => $element
                ]);
                $rendered = $this->replaceNewlineInQuotes($rendered);
                $array = Json::decodeIfJson($rendered);

                if (!is_array($array)) {
                    throw new \Exception('Custom mapping file is not valid JSON: '.$rendered);
                }

                if (array_keys($array) !== range(0, count($array) - 1)) { // Is assoc array
                    $result = array_merge([
                        'id' => (int)$element->id,
                    ], $array);
                } else {
                    $isAssoc = false;
                    $result = [];
                    foreach ($array as $item) {
                        $result[] = $item;
                    }
                }
            } else {
                throw new \Exception('Custom mapping file not found');
            }
        } else {
            $mappingData = $this->prepareMappingData($bucket->fieldMapping);

            $result = $this->parseElement($element, $bucket, $mappingData);
        }

        if (\in_array($element->getStatus(), [AssetElement::STATUS_ENABLED, EntryElement::STATUS_LIVE, CategoryElement::STATUS_ENABLED])) {
            if ($isAssoc) { // Is assoc array
                Needletail::$plugin->connection->update($bucket->handleWithPrefix, $result);
            } else {
                foreach ($result as $item) {
                    Needletail::$plugin->connection->update($bucket->handleWithPrefix, $item);
                }
            }
        } else {
            if ($isAssoc) { // Is assoc array
                Needletail::$plugin->connection->delete($bucket->handleWithPrefix, $element->getId());
            } else {
                foreach ($result as $item) {
                    Needletail::$plugin->connection->delete($bucket->handleWithPrefix, $item['id']);
                }
            }
        }
    }

    public function deleteSingle(BucketModel $bucket, ElementInterface $element = null, $elementId = null, $variants = null)
    {
        if ( $this->shouldNotPerformWriteActions() )
            return false;

        if (!is_null($variants)) {
            foreach ($variants as $item) {
                Needletail::$plugin->connection->delete($bucket->handleWithPrefix, $item);
            }
        } else {
            throw new \Exception(json_encode($variants));
            Needletail::$plugin->connection->delete($bucket->handleWithPrefix, $elementId ?? $element->getId());
        }
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
        return CRAFT_ENVIRONMENT !== 'production';
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
