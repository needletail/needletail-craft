<?php

namespace needletail\needletail\elements;

use Craft;
use craft\base\ElementInterface as CraftElementInterface;
use craft\elements\Asset as AssetElement;
use craft\elements\Category as CategoryElement;
use craft\elements\Entry as EntryElement;
use craft\elements\db\ElementQueryInterface;
use needletail\needletail\base\Element;
use needletail\needletail\base\ElementInterface;
use needletail\needletail\models\BucketModel;

class UrlResources extends Element implements ElementInterface
{
    public static $name = 'All URL resources';
    public static $class = self::class;

    public function getGroupsTemplate()
    {
        return 'needletail/_includes/elements/url-resources/groups';
    }

    public function getMappingTemplate()
    {
        return 'needletail/_includes/elements/url-resources/map';
    }

    /**
     * @inheritdoc
     *
     * UrlResources is indexed via multiple element queries. Indexing code should prefer getQueries().
     */
    public function getQuery(BucketModel $bucket, $params = [])
    {
        $queries = $this->getQueries($bucket, $params);

        if (isset($queries[0])) {
            return $queries[0];
        }

        // Fallback empty query
        $query = EntryElement::find()->id(false);
        Craft::configure($query, $params);
        return $query;
    }

    /**
     * Returns all element queries that make up this bucket type.
     *
     * @param BucketModel $bucket
     * @param array $params
     * @return ElementQueryInterface[]
     */
    public function getQueries(BucketModel $bucket, array $params = []): array
    {
        $config = $this->getConfig($bucket);
        $siteId = $this->resolveSiteId($bucket);

        $queries = [];

        if ($this->isEnabled($config, 'entries', true)) {
            $query = EntryElement::find()
                ->siteId($siteId)
                ->status(EntryElement::STATUS_LIVE);

            Craft::configure($query, $params);
            $queries[] = $query;
        }

        if ($this->isEnabled($config, 'categories', true)) {
            $query = CategoryElement::find()
                ->siteId($siteId)
                ->status(CategoryElement::STATUS_ENABLED);

            Craft::configure($query, $params);
            $queries[] = $query;
        }

        if ($this->isEnabled($config, 'assets', true)) {
            $query = AssetElement::find()
                ->siteId($siteId)
                ->status(AssetElement::STATUS_ENABLED);

            Craft::configure($query, $params);
            $queries[] = $query;
        }

        // Commerce products (optional dependency)
        if ($this->isEnabled($config, 'commerceProducts', true) && class_exists('craft\\commerce\\elements\\Product')) {
            $productClass = 'craft\\commerce\\elements\\Product';
            /** @var ElementQueryInterface $query */
            $query = $productClass::find()->siteId($siteId);

            Craft::configure($query, $params);
            $queries[] = $query;
        }

        // Calendar events (optional dependency)
        if ($this->isEnabled($config, 'calendarEvents', true) && class_exists('Solspace\\Calendar\\Elements\\Event')) {
            $eventClass = 'Solspace\\Calendar\\Elements\\Event';
            /** @var ElementQueryInterface $query */
            $query = $eventClass::find()->siteId($siteId);

            Craft::configure($query, $params);
            $queries[] = $query;
        }

        // Other element types with URIs (plugin-provided)
        if ($this->isEnabled($config, 'otherUris', true)) {
            foreach (Craft::$app->elements->getAllElementTypes() as $elementType) {
                if (!is_string($elementType)) {
                    continue;
                }

                if ($this->isKnownType($elementType)) {
                    continue;
                }

                if (!is_subclass_of($elementType, CraftElementInterface::class)) {
                    continue;
                }

                if (!$elementType::hasUris()) {
                    continue;
                }

                /** @var ElementQueryInterface $query */
                $query = Craft::$app->elements->createElementQuery($elementType);
                $query->siteId($siteId);

                Craft::configure($query, $params);
                $queries[] = $query;
            }
        }

        return $queries;
    }

    /**
     * Whether the element should be considered part of this bucket, and therefore indexed.
     *
     * @param BucketModel $bucket
     * @param CraftElementInterface $element
     * @return bool
     */
    public function includesElement(BucketModel $bucket, CraftElementInterface $element): bool
    {
        $siteId = $this->resolveSiteId($bucket);
        if (isset($element->siteId) && (int)$element->siteId !== (int)$siteId) {
            return false;
        }

        $config = $this->getConfig($bucket);

        if ($element instanceof EntryElement) {
            return $this->isEnabled($config, 'entries', true);
        }

        if ($element instanceof CategoryElement) {
            return $this->isEnabled($config, 'categories', true);
        }

        if ($element instanceof AssetElement) {
            return $this->isEnabled($config, 'assets', true);
        }

        if (class_exists('craft\\commerce\\elements\\Product') && is_a($element, 'craft\\commerce\\elements\\Product')) {
            return $this->isEnabled($config, 'commerceProducts', true);
        }

        if (class_exists('Solspace\\Calendar\\Elements\\Event') && is_a($element, 'Solspace\\Calendar\\Elements\\Event')) {
            return $this->isEnabled($config, 'calendarEvents', true);
        }

        if (!$this->isEnabled($config, 'otherUris', true)) {
            return false;
        }

        $class = get_class($element);
        return $class::hasUris();
    }

    public function shouldIndexElement(BucketModel $bucket, CraftElementInterface $element): bool
    {
        if (!$this->includesElement($bucket, $element)) {
            return false;
        }

        if (!$element->getUrl()) {
            return false;
        }

        return $this->isPublic($element);
    }

    private function resolveSiteId(BucketModel $bucket): int
    {
        return (int)($bucket->siteId ?: Craft::$app->getSites()->getPrimarySite()->id);
    }

    private function getConfig(BucketModel $bucket): array
    {
        $data = $bucket->getElementData();
        if (!is_array($data)) {
            return [];
        }

        $config = $data[self::class] ?? [];
        return is_array($config) ? $config : [];
    }

    private function isEnabled(array $config, string $key, bool $default): bool
    {
        if (!array_key_exists($key, $config)) {
            return $default;
        }

        return (bool)$config[$key];
    }

    private function isPublic(CraftElementInterface $element): bool
    {
        $status = $element->getStatus();
        if ($status === null) {
            return false;
        }

        // Keep this deliberately strict: only index elements that Craft considers publicly visible.
        return in_array($status, [EntryElement::STATUS_LIVE, CategoryElement::STATUS_ENABLED, AssetElement::STATUS_ENABLED], true);
    }

    private function isKnownType(string $elementType): bool
    {
        return in_array($elementType, [
            self::class,
            EntryElement::class,
            CategoryElement::class,
            AssetElement::class,
            'craft\\commerce\\elements\\Product',
            'Solspace\\Calendar\\Elements\\Event',
        ], true);
    }
}

