<?php

namespace needletail\needletail\services;

use craft\base\Component;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\events\ElementEvent;
use needletail\needletail\jobs\DeleteElement;
use needletail\needletail\jobs\IndexBucket;
use needletail\needletail\jobs\IndexElement;
use needletail\needletail\models\BucketModel;
use needletail\needletail\Needletail;
use yii\base\Event as YiiEvent;

class Events extends Component
{
    private $_buckets = [];

    // Public Methods
    // =========================================================================

    public function onSave(YiiEvent $event)
    {
        $element = $this->getElementFromEvent($event);

        if (!$element) {
            return;
        }

        $buckets = $this->getBucketsForElement($element);

        if (!count($buckets) )
            return;

        foreach ($buckets as $bucket ) {
            if ( Needletail::$plugin->getSettings()->processSingleElementsViaQueue ){
                \Craft::$app->getQueue()->delay(0)->push(new IndexElement([
                    'bucket' => $bucket,
                    'elementId' => $element->getId(),
                    'siteId' => $element->siteId
                ]));
            } else {
                Needletail::$plugin->process->processSingle($bucket, $element);
            }
        }
    }

    public function onUpdateSlugAndUri(ElementEvent $event)
    {
        $buckets = $this->getBucketsForElement($event->element);

        if (!count($buckets) )
            return;

        foreach ($buckets as $bucket) {
            if ( Needletail::$plugin->getSettings()->processSingleElementsViaQueue ){
                \Craft::$app->getQueue()->delay(0)->push(new IndexElement([
                    'bucket' => $bucket,
                    'elementId' => $event->element->getId(),
                    'siteId' => $event->element->siteId
                ]));
            } else {
                Needletail::$plugin->process->processSingle($bucket, $event->element);
            }
        }
    }

    public function onDelete(YiiEvent $event)
    {
        $element = $this->getElementFromEvent($event);

        if (!$element) {
            return;
        }

        $buckets = $this->getBucketsForElement($element);

        if (!count($buckets) )
            return;

        foreach ($buckets as $bucket) {
            if ( Needletail::$plugin->getSettings()->processSingleElementsViaQueue ){
                \Craft::$app->getQueue()->delay(0)->push(new DeleteElement([
                    'bucket' => $bucket,
                    'elementId' => $element->getId()
                ]));
            } else {
                Needletail::$plugin->process->deleteSingle($bucket, $element);
            }
        }
    }

    public function onRestore(ElementEvent $event)
    {
        $buckets = $this->getBucketsForElement($event->element);

        if (!count($buckets) )
            return;

        foreach ($buckets as $bucket) {
            if ( Needletail::$plugin->getSettings()->processSingleElementsViaQueue ){
                \Craft::$app->getQueue()->delay(0)->push(new IndexElement([
                    'bucket' => $bucket,
                    'elementId' => $event->element->getId(),
                    'siteId' => $event->element->siteId
                ]));
            } else {
                Needletail::$plugin->process->processSingle($bucket, $event->element);
            }
        }
    }

    // Private Methods
    // =========================================================================

    private function getBucketsForElement(ElementInterface $element) {
        if (!$this->doPreFlightChecks($element))
            return [];

        $buckets = $this->getAllBucketsForElement($element);

        return count($buckets) ? $buckets : [];
    }

    private function doPreFlightChecks(ElementInterface $element)
    {
        if (Needletail::$plugin->process->shouldNotPerformWriteActions())
            return false;

        $this->_buckets = Needletail::$plugin->buckets->getCached();

        return count($this->_buckets) > 0;
    }

    private function getElementFromEvent(YiiEvent $event): ?ElementInterface
    {
        if ($event instanceof ElementEvent) {
            return $event->element;
        }

        if (method_exists($event, 'getElement')) {
            $element = $event->getElement();

            return $element instanceof ElementInterface ? $element : null;
        }

        return null;
    }

    private function getAllBucketsForElement(\craft\base\ElementInterface $element)
    {
        return array_filter($this->_buckets, function (BucketModel $bucketModel) use ($element) {
            $elementHandler = $bucketModel->element;

            if (!$elementHandler || !method_exists($elementHandler, 'includesElement')) {
                return false;
            }

            try {
                return (bool)$elementHandler->includesElement($bucketModel, $element);
            } catch (\Throwable $e) {
                return false;
            }
        });
    }
}
