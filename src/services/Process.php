<?php

namespace needletail\needletail\services;

use craft\base\Component;
use craft\base\ElementInterface;
use craft\elements\Asset as AssetElement;
use craft\elements\Category as CategoryElement;
use craft\elements\Entry as EntryElement;
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
            $results = array_map(function (ElementInterface $element) use ($bucket) {
                if (file_exists(\Craft::$app->path->getSiteTemplatesPath().'/_needletail/'.$bucket->mappingTwigFile)) {
                    $rendered = \Craft::$app->getView()->renderString(file_get_contents(\Craft::$app->path->getSiteTemplatesPath().'/_needletail/'.$bucket->mappingTwigFile), [
                        'entry' => $element
                    ]);
                    $array = json_decode($rendered, true);

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

        Needletail::$plugin->connection->bulk($bucket->handleWithPrefix, $results);
    }

    public function processSingle(BucketModel $bucket, ElementInterface $element)
    {
        if ( $this->shouldNotPerformWriteActions() )
            return false;

        if ($bucket->customMappingFile) {
            if (file_exists(\Craft::$app->path->getSiteTemplatesPath().'/_needletail/'.$bucket->mappingTwigFile)) {
                $rendered = \Craft::$app->getView()->renderString(file_get_contents(\Craft::$app->path->getSiteTemplatesPath().'/_needletail/'.$bucket->mappingTwigFile), [
                    'entry' => $element
                ]);
                $array = json_decode($rendered, true);

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

        if (\in_array($element->getStatus(), [AssetElement::STATUS_ENABLED, EntryElement::STATUS_LIVE, CategoryElement::STATUS_ENABLED])) {
            Needletail::$plugin->connection->update($bucket->handleWithPrefix, $result);
        } else {
            Needletail::$plugin->connection->delete($bucket->handleWithPrefix, $element->getId());
        }
    }

    public function deleteSingle(BucketModel $bucket, ElementInterface $element = null, $elementId = null)
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
}
